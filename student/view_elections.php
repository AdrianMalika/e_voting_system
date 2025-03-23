<?php
require_once '../includes/header.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get user's branch
$stmt = $conn->prepare("SELECT branch FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userBranch = $stmt->fetch(PDO::FETCH_ASSOC)['branch'];

// Get requested branch
$requestedBranch = $_GET['branch'] ?? '';

// Verify user can access this branch
if ($userBranch !== $requestedBranch) {
    $_SESSION['error'] = "You can only view elections for your branch.";
    header("Location: elections.php");
    exit();
}

// Get active elections for the branch
$stmt = $conn->prepare("
    SELECT * FROM elections 
    WHERE branch = ? 
    AND status = 'active'
    ORDER BY start_date DESC
");
$stmt->execute([$userBranch]);
$elections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Log the number of elections found
error_log("Number of elections found: " . count($elections));

error_log("User Branch: " . $userBranch);
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 style="color: #2c3e50;"><i class="fas fa-vote-yea me-2"></i>Elections in <?php echo htmlspecialchars($userBranch); ?></h2>
                <a href="elections.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($elections)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            There are currently no active elections in <?php echo htmlspecialchars($userBranch); ?>.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($elections as $election): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p class="text-muted">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                            </p>
                            <p><?php echo htmlspecialchars($election['description']); ?></p>
                            <a href="vote.php?election_id=<?php echo $election['id']; ?>" 
                               class="btn" style="background-color: #2c3e50; color: white;">
                                <i class="fas fa-check-circle me-2"></i>Vote Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?> 