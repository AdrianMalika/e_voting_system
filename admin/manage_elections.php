<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
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
    } catch (Exception $e) {
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
            FROM election_candidates ec 
            WHERE ec.election_id = e.id 
            AND ec.status = 'approved'
        ) as approved_candidates,
        (
            SELECT COUNT(*) 
            FROM election_candidates ec 
            WHERE ec.election_id = e.id 
            AND ec.status = 'pending'
        ) as pending_candidates,
        CASE 
            WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'active'
            WHEN NOW() > e.end_date THEN 'completed'
            ELSE 'upcoming'
        END as display_status
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
</head>
<body>
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">Election Dashboard</h2>
                                <p class="mb-0">Manage and monitor all elections</p>
                            </div>
                            <a href="add_election.php" class="btn btn-light">
                                <i class="fas fa-plus me-2"></i>Create Election
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        <!-- Elections Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
65            <?php foreach ($elections as $election): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <!-- Status Badge -->
                            <?php
                            $statusClass = match($election['display_status']) {
                                'active' => 'success',
                                'completed' => 'secondary',
                                default => 'warning'
                            };
                            ?>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($election['title']); ?></h5>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($election['display_status']); ?>
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="card-text text-muted small mb-3">
                                <?php echo htmlspecialchars($election['description']); ?>
                            </p>

                            <!-- Timeline -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fas fa-calendar me-2"></i>
                                    <span>
                                        <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="small text-muted">Candidates</div>
                                        <div class="h5 mb-0">
                                            <?php echo isset($election['approved_candidates']) ? $election['approved_candidates'] : '0'; ?>
                                            <?php if (isset($election['pending_candidates']) && $election['pending_candidates'] > 0): ?>
                                                <span class="badge bg-warning ms-1" title="Pending Applications">
                                                    +<?php echo $election['pending_candidates']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="small text-muted">Total Votes</div>
                                        <div class="h5 mb-0">
                                            <?php echo number_format($election['total_votes'] ?? 0); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <?php if (isset($election['pending_candidates']) && $election['pending_candidates'] > 0): ?>
                                    <a href="?view_requests=<?php echo $election['id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Review Pending Applications">
                                        <i class="fas fa-user-clock"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="edit_election.php?id=<?php echo $election['id']; ?>" 
                                   class="btn btn-primary btn-sm" title="Edit Election">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if ($election['display_status'] === 'completed'): ?>
                                    <a href="view_results.php?id=<?php echo $election['id']; ?>" 
                                       class="btn btn-info btn-sm" title="View Results">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
    .card {
        transition: transform 0.2s;
        border-radius: 10px;
        border: none;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
    .btn-sm {
        padding: 0.5rem 1rem;
    }
    .bg-primary {
        background: linear-gradient(45deg, #4e73df, #224abe) !important;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
