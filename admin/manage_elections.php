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
    SELECT e.*, 
           COUNT(DISTINCT v.id) as total_votes,
           COUNT(DISTINCT ec.candidate_id) as total_candidates,
           (
               SELECT COUNT(*) 
               FROM election_candidates ec2 
               WHERE ec2.election_id = e.id 
               AND ec2.status = 'pending'
           ) as pending_requests,
           CASE 
               WHEN e.status = 'active' THEN 'active'
               WHEN e.status = 'completed' THEN 'completed'
               ELSE 'upcoming'
           END as display_status
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    LEFT JOIN election_candidates ec ON e.id = ec.election_id
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Elections</h2>
            <a href="add_election.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Election
            </a>
        </div>

        <!-- Elections Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Candidates</th>
                                <th>Total Votes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elections as $election): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($election['title']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($election['description']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($election['start_date'])); ?>
                                        <br>
                                        <small class="text-muted">to</small>
                                        <br>
                                        <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $election['display_status'] === 'active' ? 'success' : 
                                                ($election['display_status'] === 'upcoming' ? 'info' : 'secondary');
                                        ?>">
                                            <?php echo ucfirst($election['display_status']); ?>
                                        </span>
                                        <?php if ($election['pending_requests'] > 0): ?>
                                            <span class="badge bg-warning ms-2">
                                                <?php echo $election['pending_requests']; ?> pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $election['total_candidates']; ?></td>
                                    <td><?php echo number_format($election['total_votes']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($election['pending_requests'] > 0): ?>
                                                <a href="?view_requests=<?php echo $election['id']; ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-user-clock me-1"></i>
                                                    View Requests (<?php echo $election['pending_requests']; ?>)
                                                </a>
                                            <?php endif; ?>
                                            <a href="edit_election.php?id=<?php echo $election['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="view_results.php?id=<?php echo $election['id']; ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-chart-bar me-1"></i>Results
                                            </a>
                                            <!-- Delete Button -->
                                            <form method="POST" action="manage_elections.php" class="d-inline">
                                                <input type="hidden" name="election_id" value="<?php echo $election['id']; ?>">
                                                <button type="submit" name="delete_election" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to delete this election?');">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Candidate Requests Section -->
        <?php if (!empty($candidate_requests)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Pending Requests for <?php echo htmlspecialchars($candidate_requests[0]['election_title']); ?>
                        </h5>
                        <a href="manage_elections.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times me-2"></i>Close Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($candidate_requests as $request): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex mb-3">
                                            <?php if ($request['photo_url']): ?>
                                                <img src="<?php echo '../' . htmlspecialchars($request['photo_url']); ?>" 
                                                     class="rounded-circle me-3" 
                                                     style="width: 64px; height: 64px; object-fit: cover;"
                                                     alt="<?php echo htmlspecialchars($request['candidate_name']); ?>">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3"
                                                     style="width: 64px; height: 64px;">
                                                    <i class="fas fa-user fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($request['candidate_name']); ?>
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    <?php echo htmlspecialchars($request['position']); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($request['email']); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                Requested on <?php echo date('M j, Y g:i A', strtotime($request['request_date'])); ?>
                                            </small>
                                        </div>

                                        <h6>Manifesto</h6>
                                        <p class="card-text">
                                            <?php echo nl2br(htmlspecialchars($request['manifesto'])); ?>
                                        </p>

                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="election_id" 
                                                   value="<?php echo $request['election_id']; ?>">
                                            <input type="hidden" name="candidate_id" 
                                                   value="<?php echo $request['candidate_id']; ?>">
                                            <input type="hidden" name="handle_request" value="1">
                                            
                                            <div class="btn-group w-100">
                                                <button type="submit" name="action" value="approve" 
                                                        class="btn btn-success">
                                                    <i class="fas fa-check me-2"></i>Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" 
                                                        class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i>Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php require_once '../includes/footer.php'; ?>
