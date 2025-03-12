<?php
require_once '../includes/header.php';

// Ensure user is a candidate
if (!isset($_SESSION['user_id']) || $user['role'] !== 'candidate') {
    $_SESSION['error'] = "Unauthorized access. Please login as a candidate.";
    header("Location: ../login.php");
    exit();
}

// Handle election join requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_election'])) {
    try {
        $election_id = $_POST['election_id'];
        
        // Check if already requested
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM election_candidates 
            WHERE election_id = ? AND candidate_id = (
                SELECT id FROM candidates WHERE user_id = ?
            )
        ");
        $stmt->execute([$election_id, $_SESSION['user_id']]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("You have already requested or been added to this election");
        }

        // Get candidate ID
        $stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $candidate_id = $stmt->fetchColumn();

        if (!$candidate_id) {
            throw new Exception("You must complete your candidate profile first");
        }

        // Add request
        $stmt = $conn->prepare("
            INSERT INTO election_candidates (election_id, candidate_id, status)
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$election_id, $candidate_id]);

        // Log the request
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address)
            VALUES (?, 'REQUEST_JOIN_ELECTION', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            "Requested to join election ID: $election_id",
            $_SERVER['REMOTE_ADDR']
        ]);

        $_SESSION['success'] = "Successfully requested to join the election";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit();
}

// Handle election request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_election'])) {
    try {
        $election_id = $_POST['election_id'];
        
        // Check if already requested
        $stmt = $conn->prepare("
            SELECT id FROM election_candidates 
            WHERE election_id = ? AND candidate_id = ?
        ");
        $stmt->execute([$election_id, $candidate_info['id']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("You have already requested to join this election");
        }

        // Insert request
        $stmt = $conn->prepare("
            INSERT INTO election_candidates (election_id, candidate_id, status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->execute([$election_id, $candidate_info['id']]);

        // Log the request
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address)
            VALUES (?, 'ELECTION_REQUEST', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            "Requested to join election ID: $election_id",
            $_SERVER['REMOTE_ADDR']
        ]);

        $_SESSION['success'] = "Your request to join the election has been submitted";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit();
}

// Get candidate information
$stmt = $conn->prepare("
    SELECT c.*, u.name as candidate_name
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$candidate_info = $stmt->fetch();

// Get current elections
$current_elections = [];
if ($candidate_info) {
    $stmt = $conn->prepare("
        SELECT e.*, 
               CASE 
                   WHEN NOW() < e.start_date THEN 'upcoming'
                   WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'active'
                   ELSE 'ended'
               END as current_status
        FROM elections e
        JOIN election_candidates ec ON e.id = ec.election_id
        WHERE ec.candidate_id = ?
        ORDER BY e.start_date DESC
    ");
    $stmt->execute([$candidate_info['id']]);
    $current_elections = $stmt->fetchAll();
}

// Get available elections for candidates
$available_elections = [];
if ($candidate_info && $candidate_info['status'] === 'approved') {
    $stmt = $conn->prepare("
        SELECT e.*,
               COALESCE(
                   (SELECT ec2.status 
                    FROM election_candidates ec2 
                    WHERE ec2.election_id = e.id 
                    AND ec2.candidate_id = ?), 
                   'not_requested'
               ) as request_status
        FROM elections e
        WHERE e.status IN ('upcoming', 'active')
        ORDER BY e.start_date ASC
    ");
    $stmt->execute([$candidate_info['id']]);
    $available_elections = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard - E-Voting System</title>
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

        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($candidate_info['candidate_name']); ?>!</h2>

        <!-- Candidate Status Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Your Candidate Status</h5>
            </div>
            <div class="card-body">
                <?php if ($candidate_info): ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($candidate_info['photo_url']): ?>
                                <img src="<?php echo '../' . htmlspecialchars($candidate_info['photo_url']); ?>" 
                                     class="img-fluid rounded" alt="Your photo">
                            <?php else: ?>
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($candidate_info['position']); ?></h4>
                            <p class="text-muted mb-3">Status: 
                                <span class="badge bg-<?php 
                                    echo $candidate_info['status'] === 'approved' ? 'success' : 
                                        ($candidate_info['status'] === 'pending' ? 'warning' : 'danger');
                                ?>">
                                    <?php echo ucfirst($candidate_info['status']); ?>
                                </span>
                            </p>
                            <h5>Your Manifesto</h5>
                            <p><?php echo nl2br(htmlspecialchars($candidate_info['manifesto'] ?? '')); ?></p>
                            <a href="update_profile.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Update Profile
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        You haven't completed your candidate profile yet. 
                        <a href="update_profile.php">Complete it now</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Elections -->
        <?php if (!empty($current_elections)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Your Elections</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Election</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_elections as $election): ?>
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
                                                echo $election['current_status'] === 'active' ? 'success' : 
                                                    ($election['current_status'] === 'upcoming' ? 'info' : 'secondary');
                                            ?>">
                                                <?php echo ucfirst($election['current_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Elections -->
        <?php if ($candidate_info && $candidate_info['status'] === 'approved' && !empty($available_elections)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Available Elections</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Election</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($available_elections as $election): ?>
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
                                                echo $election['status'] === 'active' ? 'success' : 
                                                    ($election['status'] === 'upcoming' ? 'info' : 'secondary');
                                            ?>">
                                                <?php echo ucfirst($election['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($election['request_status'] === 'not_requested'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="election_id" value="<?php echo $election['id']; ?>">
                                                    <button type="submit" name="request_election" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus"></i> Request to Join
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-<?php 
                                                    echo $election['request_status'] === 'approved' ? 'success' : 
                                                        ($election['request_status'] === 'pending' ? 'warning' : 'danger');
                                                ?>">
                                                    <?php echo ucfirst($election['request_status']); ?>
                                                </span>
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
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
