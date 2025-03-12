<?php
require_once '../includes/header.php';

// Ensure user is a candidate
if (!isset($_SESSION['user_id']) || $user['role'] !== 'candidate') {
    $_SESSION['error'] = "Unauthorized access. Please login as a candidate.";
    header("Location: ../login.php");
    exit();
}

// Get candidate ID
$stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$candidate_id = $stmt->fetchColumn();

if (!$candidate_id) {
    $_SESSION['error'] = "Candidate profile not found";
    header("Location: dashboard.php");
    exit();
}

// Get elections the candidate participated in
$stmt = $conn->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.candidate_id = ec.candidate_id) as my_votes,
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) as total_votes,
           (SELECT COUNT(*) FROM election_candidates ec2 WHERE ec2.election_id = e.id) as total_candidates,
           CASE 
               WHEN e.status = 'completed' THEN 'ended'
               WHEN NOW() < e.start_date THEN 'upcoming'
               WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'active'
               ELSE 'ended'
           END as status
    FROM elections e
    JOIN election_candidates ec ON e.id = ec.election_id
    WHERE ec.candidate_id = ?
    ORDER BY e.end_date DESC
");
$stmt->execute([$candidate_id]);
$elections = $stmt->fetchAll();

// If election_id is provided, get detailed results
$election_details = null;
if (isset($_GET['election_id'])) {
    $stmt = $conn->prepare("
        SELECT e.*, 
               c.position, c.photo_url,
               u.name as candidate_name,
               COUNT(v.id) as vote_count,
               (SELECT COUNT(*) FROM votes WHERE election_id = e.id) as total_votes
        FROM elections e
        JOIN election_candidates ec ON e.id = ec.election_id
        JOIN candidates c ON ec.candidate_id = c.id
        JOIN users u ON c.user_id = u.id
        LEFT JOIN votes v ON v.election_id = e.id AND v.candidate_id = c.id
        WHERE e.id = ?
        GROUP BY c.id
        ORDER BY vote_count DESC
    ");
    $stmt->execute([$_GET['election_id']]);
    $election_details = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - E-Voting System</title>
    <style>
        .progress {
            height: 25px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Election Results</h2>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if (empty($elections)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>You haven't participated in any elections yet.
            </div>
        <?php else: ?>
            <!-- Elections Overview -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                <?php foreach ($elections as $election): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($election['title']); ?></h5>
                                <p class="text-muted small mb-2">
                                    <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                                </p>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php 
                                        echo $election['status'] === 'active' ? 'success' : 
                                            ($election['status'] === 'upcoming' ? 'info' : 'secondary');
                                    ?>">
                                        <?php echo ucfirst($election['status']); ?>
                                    </span>
                                </div>

                                <?php if ($election['status'] === 'ended'): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Your Votes</span>
                                            <span class="fw-bold">
                                                <?php 
                                                    $percentage = $election['total_votes'] > 0 
                                                        ? ($election['my_votes'] / $election['total_votes']) * 100 
                                                        : 0;
                                                    echo number_format($percentage, 1) . '%';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%"
                                                 aria-valuenow="<?php echo $percentage; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $election['my_votes']; ?> / <?php echo $election['total_votes']; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <a href="?election_id=<?php echo $election['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-chart-bar me-2"></i>View Detailed Results
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">
                                        Results will be available after the election ends.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Detailed Results -->
            <?php if ($election_details): ?>
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo htmlspecialchars($election_details[0]['title']); ?> - Detailed Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Candidate</th>
                                        <th>Position</th>
                                        <th>Votes</th>
                                        <th style="width: 40%;">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($election_details as $index => $candidate): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($candidate['photo_url']): ?>
                                                        <img src="<?php echo '../' . htmlspecialchars($candidate['photo_url']); ?>" 
                                                             class="rounded-circle me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                             alt="<?php echo htmlspecialchars($candidate['candidate_name']); ?>">
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center me-2"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($candidate['candidate_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                            <td><?php echo number_format($candidate['vote_count']); ?></td>
                                            <td>
                                                <?php 
                                                    $percentage = $candidate['total_votes'] > 0 
                                                        ? ($candidate['vote_count'] / $candidate['total_votes']) * 100 
                                                        : 0;
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?php echo $percentage; ?>%"
                                                             aria-valuenow="<?php echo $percentage; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted" style="min-width: 60px;">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
