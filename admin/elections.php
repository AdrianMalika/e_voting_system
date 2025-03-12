<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get current time
$now = date('Y-m-d H:i:s');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_election'])) {
        // Validate and sanitize input
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Format dates for database
        $start_datetime = date('Y-m-d H:i:s', strtotime($start_date));
        $end_datetime = date('Y-m-d H:i:s', strtotime($end_date));
        
        if (!$title || !$description || !$start_date || !$end_date) {
            $_SESSION['error'] = "All fields are required";
        } elseif (strtotime($start_date) >= strtotime($end_date)) {
            $_SESSION['error'] = "End date must be after start date";
        } elseif (strtotime($start_date) < time()) {
            $_SESSION['error'] = "Start date cannot be in the past";
        } else {
            try {
                $conn->beginTransaction();
                
                // Set initial status
                $status = strtotime($start_date) > time() ? 'upcoming' : 'active';
                
                // Insert election
                $stmt = $conn->prepare("
                    INSERT INTO elections (title, description, start_date, end_date, status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $description, $start_datetime, $end_datetime, $status, $user['id']]);
                
                // Get the ID of the newly created election
                $election_id = $conn->lastInsertId();
                
                // Create notification for admin
                add_notification(
                    $conn,
                    'new_election',
                    "New election created: $title (Starting: " . date('M j, Y', strtotime($start_datetime)) . ")",
                    $election_id,
                    'election',
                    $user['id']  // Admin's notification
                );
                
                // Debug: Print current user info
                error_log("Admin creating election - User ID: " . $user['id'] . ", Role: " . $user['role']);
                
                // Get all students and create notifications for them
                $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'student'");
                $stmt->execute();
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Debug: Print found students
                error_log("Found " . count($students) . " students");
                foreach ($students as $student) {
                    error_log("Creating notification for student - ID: " . $student['id'] . ", Username: " . $student['username']);
                    
                    // Create notification for each student
                    add_notification(
                        $conn,
                        'new_election',
                        "A new election '$title' has been created and will start on " . date('M j, Y', strtotime($start_datetime)),
                        $election_id,
                        'election',
                        $student['id']  // Student's notification
                    );
                }
                
                $conn->commit();
                $_SESSION['success'] = "Election created successfully";
            } catch (Exception $e) {
                $conn->rollBack();
                error_log("Error creating election: " . $e->getMessage());
                $_SESSION['error'] = "Error creating election: " . $e->getMessage();
            }
        }
        
        header("Location: elections.php");
        exit();
    }
}

$stmt = $conn->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM election_candidates ec WHERE ec.election_id = e.id) as total_candidates,
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) as total_votes
    FROM elections e
    ORDER BY e.created_at DESC
");

$stmt->execute();
$elections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Elections</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createElectionModal">
            <i class="fas fa-plus me-2"></i>Create Election
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Candidates</th>
                            <th>Votes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($elections)): ?>
                            <?php foreach ($elections as $election): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($election['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($election['description'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo date('M j, Y g:i a', strtotime($election['start_date'])); ?></td>
                                    <td><?php echo date('M j, Y g:i a', strtotime($election['end_date'])); ?></td>
                                    <td>
                                        <?php
                                            $statusClass = 'secondary';
                                            if ($election['status'] == 'upcoming') {
                                                $statusClass = 'info';
                                            } else if ($election['status'] == 'active') {
                                                $statusClass = 'success';
                                            } else if ($election['status'] == 'completed') {
                                                $statusClass = 'dark';
                                            }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($election['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $election['total_candidates']; ?></td>
                                    <td><?php echo $election['total_votes']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit_election.php?id=<?php echo $election['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_election_candidates.php?election_id=<?php echo $election['id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="view_results.php?id=<?php echo $election['id']; ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No elections found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Election Modal -->
<div class="modal fade" id="createElectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Election</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="create_election" value="1">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Election</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
