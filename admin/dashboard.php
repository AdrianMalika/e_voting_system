<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Please login as an administrator.";
    header("Location: ../login.php");
    exit();
}

// Get quick stats
$stats = [];

// Total Users
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$stats['students'] = $stmt->fetchColumn();

// Total Candidates
$stmt = $conn->query("SELECT COUNT(*) FROM candidates");
$stats['candidates'] = $stmt->fetchColumn();

// Pending Candidates
$stmt = $conn->query("SELECT COUNT(*) FROM candidates WHERE status = 'pending'");
$stats['pending_candidates'] = $stmt->fetchColumn();

// Active Elections
$stmt = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'");
$stats['active_elections'] = $stmt->fetchColumn();

// Total Votes
$stmt = $conn->query("SELECT COUNT(*) FROM votes");
$stats['total_votes'] = $stmt->fetchColumn();

// Recent Activities
$stmt = $conn->prepare("
    SELECT al.*, u.name, u.email 
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Voting System</title>
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .action-card {
            height: 100%;
            transition: all 0.3s;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card shadow-sm stat-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Students</h6>
                        <h3 class="mb-0"><?php echo $stats['students']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card shadow-sm stat-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Candidates</h6>
                        <h3 class="mb-0"><?php echo $stats['candidates']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card shadow-sm stat-card bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Pending Candidates</h6>
                        <h3 class="mb-0"><?php echo $stats['pending_candidates']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card shadow-sm stat-card bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Active Elections</h6>
                        <h3 class="mb-0"><?php echo $stats['active_elections']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card shadow-sm stat-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Votes</h6>
                        <h3 class="mb-0"><?php echo $stats['total_votes']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="manage_elections.php" class="text-decoration-none">
                    <div class="card shadow-sm action-card">
                        <div class="card-body text-center p-4">
                            <div class="action-icon text-primary">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <h5>Manage Elections</h5>
                            <p class="text-muted mb-0">Create and manage elections</p>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-md-4 mb-3">
                <a href="candidates.php" class="text-decoration-none">
                    <div class="card shadow-sm action-card border-primary">
                        <div class="card-body text-center p-4">
                            <div class="action-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5>Manage Candidates</h5>
                            <p class="text-muted mb-0">Approve candidates and add them to elections</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Recent Activity -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></td>
                                    <td>
                                        <?php if ($activity['user_id']): ?>
                                            <?php echo htmlspecialchars($activity['name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($activity['email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($activity['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['details']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="audit_logs.php" class="btn btn-sm btn-primary">
                        View All Activities
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    

</body>
</html>

<?php require_once '../includes/footer.php'; ?>
