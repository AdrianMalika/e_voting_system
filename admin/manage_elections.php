<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}

// Add this near the top of your file, after the database connection
if (!$conn) {
    error_log("Database connection failed");
}

// Add this temporarily at the top of your file to debug
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data: " . print_r($_POST, true));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle candidate request approval/rejection
        if (isset($_POST['handle_request'])) {
            $election_id = $_POST['election_id'];
            $candidate_id = $_POST['candidate_id'];
            $action = $_POST['action']; // 'approve' or 'reject'
            
            if (!in_array($action, ['approve', 'reject'])) {
                throw new Exception("Invalid action");
            }

            $new_status = $action === 'approve' ? 'approved' : 'rejected';
            
            // Update request status
            $stmt = $conn->prepare("
                UPDATE election_candidates 
                SET status = ? 
                WHERE election_id = ? AND candidate_id = ?
            ");
            $stmt->execute([$new_status, $election_id, $candidate_id]);

            // Log the action
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                'CANDIDATE_REQUEST_' . strtoupper($action),
                "Election ID: $election_id, Candidate ID: $candidate_id",
                $_SERVER['REMOTE_ADDR']
            ]);

            $_SESSION['success'] = "Candidate request has been " . $action . "ed";
        }

        // Deletion Logic in PHP
        if (isset($_POST['delete_election'])) {
            $election_id = $_POST['election_id'];

            // Validate election ID
            $stmt = $conn->prepare("SELECT id FROM elections WHERE id = ?");
            $stmt->execute([$election_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Election not found.");
            }

            // Delete election-related data
            $conn->beginTransaction();
            $conn->prepare("DELETE FROM votes WHERE election_id = ?")->execute([$election_id]);
            $conn->prepare("DELETE FROM election_candidates WHERE election_id = ?")->execute([$election_id]);
            $conn->prepare("DELETE FROM elections WHERE id = ?")->execute([$election_id]);
            $conn->commit();

            $_SESSION['success'] = "Election deleted successfully.";
        }

        // Edit election
        if (isset($_POST['edit_election'])) {
            try {
                $election_id = $_POST['election_id'];
                $title = trim($_POST['title']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $status = $_POST['status'];
                $branch = trim($_POST['branch']);
                
                // Add error logging
                error_log("Editing election: " . print_r($_POST, true));

                if (empty($title)) {
                    throw new Exception("Election title cannot be empty");
                }

                if (empty($branch)) {
                    throw new Exception("Branch cannot be empty");
                }

                // Get current election data to preserve branch
                $stmt = $conn->prepare("SELECT branch FROM elections WHERE id = ?");
                $stmt->execute([$election_id]);
                $current_election = $stmt->fetch();
                
                if (!$current_election) {
                    throw new Exception("Election not found");
                }

                // Validate dates
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                
                if ($start_timestamp === false || $end_timestamp === false) {
                    throw new Exception("Invalid date format");
                }

                if ($end_timestamp < $start_timestamp) {
                    throw new Exception("End date cannot be before start date");
                }

                // Format dates for MySQL
                $formatted_start_date = date('Y-m-d H:i:s', $start_timestamp);
                $formatted_end_date = date('Y-m-d H:i:s', $end_timestamp);

                $stmt = $conn->prepare("
                    UPDATE elections 
                    SET title = ?, 
                        start_date = ?, 
                        end_date = ?, 
                        status = ?,
                        branch = ?,
                        manual_status = 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                
                $result = $stmt->execute([
                    $title,
                    $formatted_start_date,
                    $formatted_end_date,
                    $status,
                    $branch,
                    $election_id
                ]);

                if (!$result) {
                    error_log("Update failed: " . implode(", ", $stmt->errorInfo()));
                    throw new Exception("Failed to update election: " . implode(", ", $stmt->errorInfo()));
                }

                $_SESSION['success'] = "Election updated successfully";
                
            } catch (Exception $e) {
                error_log("Election update error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
            }
            
            header("Location: manage_elections.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Election update error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: manage_elections.php" . (isset($_GET['view_requests']) ? "?view_requests=" . $_GET['view_requests'] : ""));
    exit();
}

// Get all elections with their details
$stmt = $conn->prepare("
    SELECT 
        e.*,
        COUNT(DISTINCT v.id) as total_votes,
        (
            SELECT COUNT(*) 
            FROM nominations n 
            WHERE n.election_id = e.id 
            AND n.status = 'approved'
        ) as approved_candidates,
        (
            SELECT COUNT(*) 
            FROM nominations n 
            WHERE n.election_id = e.id 
            AND n.status = 'pending'
        ) as pending_candidates,
        (
            SELECT COUNT(DISTINCT voter_id) 
            FROM votes 
            WHERE election_id = e.id
        ) as participating_students
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    GROUP BY e.id
    ORDER BY e.start_date ASC
");
$stmt->execute();
$elections = $stmt->fetchAll();

// If viewing a specific election's requests
$candidate_requests = [];
if (isset($_GET['view_requests']) && is_numeric($_GET['view_requests'])) {
    $stmt = $conn->prepare("
        SELECT ec.*, e.title as election_title, 
               c.position, c.photo_url, c.manifesto,
               u.name as candidate_name, u.email,
               ec.created_at as request_date
        FROM election_candidates ec
        JOIN elections e ON ec.election_id = e.id
        JOIN candidates c ON ec.candidate_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE ec.election_id = ? AND ec.status = 'pending'
        ORDER BY ec.created_at DESC
    ");
    $stmt->execute([$_GET['view_requests']]);
    $candidate_requests = $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - E-Voting System</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-gradient-primary text-white p-4 rounded-3 shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="display-6 mb-1">Election Management</h2>
                            <p class="lead mb-0">Monitor and manage all election activities</p>
                        </div>
                        <div class="d-flex gap-2">

                            <a href="add_election.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Create Election
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Election Status Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary active" data-filter="all">All Elections</button>
                            <button class="btn btn-outline-warning" data-filter="upcoming">Upcoming</button>
                            <button class="btn btn-outline-success" data-filter="active">Active</button>
                            <button class="btn btn-outline-secondary" data-filter="completed">Completed</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Elections Grid -->
        <div class="row g-4">
            <?php foreach ($elections as $election): ?>
            <div class="col-12 col-md-6 col-xl-4 election-card" data-status="<?php echo $election['status']; ?>">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($election['title']); ?></h5>
                             <?php 
$badge_class = 'warning'; // Default class

if ($election['status'] === 'active') {
    $badge_class = 'success';
} elseif ($election['status'] === 'completed') {
    $badge_class = 'secondary';
} elseif ($election['status'] === 'upcoming') {
    $badge_class = 'warning';
}
?>

<span class="badge bg-<?php echo $badge_class; ?> rounded-pill">
    <?php echo ucfirst($election['status']); ?>
                            </div>
                            <div>
                                <button type="button" class="btn btn-link text-primary p-0" 
                                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($election)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>

                        <p class="card-text text-muted small mb-3">
                            <?php echo htmlspecialchars($election['description']); ?>
                        </p>

                        <div class="d-flex align-items-center text-muted small mb-3">
                            <i class="fas fa-calendar me-2"></i>
                            <span>
                                <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                            </span>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-primary text-white rounded-circle me-2">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div class="small text-muted">Candidates</div>
                                            <div class="fw-bold">
                                                <?php echo $election['approved_candidates']; ?>
                                                <?php if ($election['pending_candidates'] > 0): ?>
                                                <span class="badge bg-warning ms-1">+<?php echo $election['pending_candidates']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-success text-white rounded-circle me-2">
                                            <i class="fas fa-vote-yea"></i>
                                        </div>
                                        <div>
                                            <div class="small text-muted">Total Votes</div>
                                            <div class="fw-bold"><?php echo number_format($election['total_votes'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <a href="view_election.php?election_id=<?php echo $election['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-users me-2"></i>View Candidates
                                </a>
                            </div>
                            <div class="col-6">
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this election? This action cannot be undone.');">
                                    <input type="hidden" name="election_id" value="<?php echo $election['id']; ?>">
                                    <button type="submit" name="delete_election" class="btn btn-danger btn-sm w-100">
                                        <i class="fas fa-trash-alt me-2"></i>Delete Election
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Participants Modal -->
    <div class="modal fade" id="participantsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Election Participants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="participantsList" class="table-responsive">
                        <!-- Participants will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Election Modal -->
    <div class="modal fade" id="editElectionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6">
                        <i class="fas fa-edit me-2"></i>
                        Edit Election
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editElectionForm">
                    <div class="modal-body p-3">
                        <input type="hidden" name="election_id" id="edit_election_id">
                        
                        <!-- Title Input -->
                        <div class="mb-3">
                            <label for="edit_title" class="form-label small fw-bold">
                                <i class="fas fa-heading me-1 text-primary"></i>
                                Election Title
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_title" 
                                   name="title" 
                                   required>
                        </div>

                        <!-- Branch Input -->
                        <div class="mb-3">
                            <label for="edit_branch" class="form-label small fw-bold">
                                <i class="fas fa-code-branch me-1 text-primary"></i>
                                Branch
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_branch" 
                                   name="branch" 
                                   required>
                        </div>

                        <!-- Date Inputs Grid -->
                        <div class="row g-2 mb-3">
                            <!-- Start Date -->
                            <div class="col-12 col-sm-6">
                                <label for="edit_start_date" class="form-label small fw-bold">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    Start Date
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="edit_start_date" 
                                       name="start_date" 
                                       required>
                            </div>

                            <!-- End Date -->
                            <div class="col-12 col-sm-6">
                                <label for="edit_end_date" class="form-label small fw-bold">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    End Date
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="edit_end_date" 
                                       name="end_date" 
                                       required>
                            </div>
                        </div>

                        <!-- Status Select -->
                        <div class="mb-3">
                            <label for="edit_status" class="form-label small fw-bold">
                                <i class="fas fa-flag me-1 text-primary"></i>
                                Status
                            </label>
                            <select class="form-select" 
                                    id="edit_status" 
                                    name="status" 
                                    required>
                                <option value="upcoming">Upcoming</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="submit" 
                                name="edit_election" 
                                class="btn btn-sm btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .bg-gradient-primary {
        background: linear-gradient(45deg, #2c3e50, #3498db);
    }

    .shadow-hover {
        transition: all 0.3s ease;
    }

    .shadow-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
    }

    .btn-outline-primary.active {
        background-color: #2c3e50;
        border-color: #2c3e50;
        color: white;
    }

    .election-card {
        transition: opacity 0.3s ease;
    }

    .election-card.hidden {
        display: none;
    }

    .modal.fade .modal-dialog {
        transform: scale(0.95) translateY(-30px);
        transition: transform 0.2s ease-out;
    }

    .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    .form-control, .form-select {
        transition: all 0.2s ease;
    }

    .form-control:hover, .form-select:hover {
        border-color: #6366f1;
    }

    /* Custom scrollbar for the modal */
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(99, 102, 241, 0.5) transparent;
    }

    .modal-content::-webkit-scrollbar {
        width: 6px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background-color: rgba(99, 102, 241, 0.5);
        border-radius: 3px;
    }

    /* Custom styles for the modal */
    .modal-content {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        background: linear-gradient(45deg, #2c3e50, #3498db);
    }

    .form-control, .form-select {
        font-size: 0.9rem;
        padding: 0.375rem 0.75rem;
    }

    .form-label {
        margin-bottom: 0.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .form-control, .form-select {
            font-size: 16px; /* Prevents zoom on mobile */
        }
    }

    .btn-primary {
        background: linear-gradient(45deg, #2c3e50, #3498db);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .input-group .form-select:focus,
    .input-group .form-control:focus {
        z-index: 3;
    }

    .form-select option {
        padding: 8px;
    }

    .form-select option:hover {
        background-color: #f8f9fa;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter buttons functionality
        const filterButtons = document.querySelectorAll('[data-filter]');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active state of buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter cards
                const filter = this.dataset.filter;
                const cards = document.querySelectorAll('.election-card');
                
                cards.forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });
    });

    function viewParticipants(electionId) {
        const participantsList = document.getElementById('participantsList');
        participantsList.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        fetch(`get_participants.php?election_id=${electionId}`)
            .then(response => response.json())
            .then(data => {
                let html = `
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Voted On</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.forEach(participant => {
                    html += `
                        <tr>
                            <td>${participant.name}</td>
                            <td>${participant.email}</td>
                            <td>${participant.voted_at}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                participantsList.innerHTML = html;
            })
            .catch(error => {
                participantsList.innerHTML = '<div class="alert alert-danger">Error loading participants</div>';
            });
    }

    function openEditModal(election) {
        console.log('Opening edit modal with election:', election);
        
        document.getElementById('edit_election_id').value = election.id;
        document.getElementById('edit_title').value = election.title;
        document.getElementById('edit_branch').value = election.branch;
        
        // Format the dates for datetime-local input
        const startDate = new Date(election.start_date);
        const endDate = new Date(election.end_date);
        
        document.getElementById('edit_start_date').value = startDate.toISOString().slice(0, 16);
        document.getElementById('edit_end_date').value = endDate.toISOString().slice(0, 16);
        document.getElementById('edit_status').value = election.status;
        
        new bootstrap.Modal(document.getElementById('editElectionModal')).show();
    }

    // Add form submission validation
    document.getElementById('editElectionForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('edit_start_date').value);
        const endDate = new Date(document.getElementById('edit_end_date').value);
        
        if (endDate < startDate) {
            e.preventDefault();
            alert('End date cannot be before start date');
        }
    });
    </script>

</body>
</html>

<?php require_once '../includes/footer.php'; ?>
