<?php
require_once '../includes/header.php';

// Ensure user is a candidate
if (!isset($_SESSION['user_id']) || $user['role'] !== 'candidate') {
    $_SESSION['error'] = "Unauthorized access. Please login as a candidate.";
    header("Location: ../login.php");
    exit();
}

// Get candidate information with user details
$stmt = $conn->prepare("
    SELECT c.*, u.name, u.email
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$candidate = $stmt->fetch();

// Get election participation history
$stmt = $conn->prepare("
    SELECT e.title, e.start_date, e.end_date,
           CASE 
               WHEN NOW() < e.start_date THEN 'upcoming'
               WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'active'
               ELSE 'ended'
           END as status,
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.candidate_id = ec.candidate_id) as vote_count
    FROM elections e
    JOIN election_candidates ec ON e.id = ec.election_id
    WHERE ec.candidate_id = ?
    ORDER BY e.start_date DESC
");
$stmt->execute([$candidate['id']]);
$elections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile - E-Voting System</title>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4">
                <!-- Profile Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <?php if ($candidate['photo_url']): ?>
                            <img src="<?php echo '../' . htmlspecialchars($candidate['photo_url']); ?>" 
                                 class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user-circle fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>

                        <h4><?php echo htmlspecialchars($candidate['name']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($candidate['position']); ?></p>
                        
                        <div class="mb-3">
                            <span class="badge bg-<?php 
                                echo $candidate['status'] === 'approved' ? 'success' : 
                                    ($candidate['status'] === 'pending' ? 'warning' : 'danger');
                            ?>">
                                <?php echo ucfirst($candidate['status']); ?>
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="update_profile.php" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <div><?php echo htmlspecialchars($candidate['email']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Manifesto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Campaign Manifesto</h5>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                    </div>
                </div>

                <!-- Election History -->
                <?php if (!empty($elections)): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Election History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Election</th>
                                            <th>Period</th>
                                            <th>Status</th>
                                            <th>Votes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($elections as $election): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($election['title']); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y', strtotime($election['start_date'])); ?>
                                                    <br>
                                                    <small class="text-muted">to</small>
                                                    <br>
                                                    <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $election['status'] === 'active' ? 'success' : 
                                                            ($election['status'] === 'upcoming' ? 'info' : 'secondary');
                                                    ?>">
                                                        <?php echo ucfirst($election['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($election['status'] === 'ended'): ?>
                                                        <?php echo number_format($election['vote_count']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
