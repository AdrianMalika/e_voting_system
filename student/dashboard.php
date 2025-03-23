<?php
require_once '../includes/header.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="bg-custom-primary text-white p-4 rounded-3 shadow">
                <h1 class="display-4 fw-bold">Student Dashboard</h1>
                <p class="lead mb-0">Welcome to your voting dashboard</p>
           
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick Stats -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="text-custom-primary mb-3">
                        <i class="fas fa-vote-yea fa-3x"></i>
                    </div>
                    <h5 class="card-title">Active Elections</h5>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM elections WHERE status = 'active'");
                    $stmt->execute();
                    $active_elections = $stmt->fetchColumn();
                    ?>
                    <h2 class="display-4 mb-3"><?php echo $active_elections; ?></h2>
                    <a href="elections.php" class="btn btn-custom-primary w-100">
                        <i class="fas fa-arrow-right me-2"></i>View Elections
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="text-custom-primary mb-3">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <h5 class="card-title">Your Votes</h5>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(DISTINCT election_id) FROM votes WHERE voter_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $votes_cast = $stmt->fetchColumn();
                    ?>
                    <h2 class="display-4 mb-3"><?php echo $votes_cast; ?></h2>
                    <a href="#" class="btn btn-custom-primary w-100">
                        <i class="fas fa-history me-2"></i>View History
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="text-custom-primary mb-3">
                        <i class="fas fa-calendar-alt fa-3x"></i>
                    </div>
                    <h5 class="card-title">Upcoming Elections</h5>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM elections WHERE status = 'upcoming'");
                    $stmt->execute();
                    $upcoming_elections = $stmt->fetchColumn();
                    ?>
                    <h2 class="display-4 mb-3"><?php echo $upcoming_elections; ?></h2>
                    <a href="elections.php" class="btn btn-custom-primary w-100">
                        <i class="fas fa-calendar me-2"></i>View Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Elections -->
    <div class="card border-0 shadow mt-5">
        <div class="card-header bg-custom-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Elections</h5>
        </div>
        <div class="card-body p-4">
            <?php
            $stmt = $conn->prepare("
                SELECT e.*, 
                       (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.voter_id = ?) as has_voted
                FROM elections e
                WHERE e.status IN ('active', 'completed')
                ORDER BY e.end_date DESC
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $recent_elections = $stmt->fetchAll();
            ?>
            
            <?php if (!empty($recent_elections)): ?>
                <div class="list-group">
                    <?php foreach ($recent_elections as $election): ?>
                        <div class="list-group-item list-group-item-action border mb-3 rounded-3 shadow-sm">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h5 class="mb-1 text-custom-primary">
                                    <?php echo htmlspecialchars($election['title']); ?>
                                </h5>
                                <div>
                                    <?php if ($election['status'] === 'active'): ?>
                                        <span class="badge bg-success"><i class="fas fa-circle me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-check-circle me-1"></i>Completed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="mb-3"><?php echo htmlspecialchars(substr($election['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Ends: <?php echo date('M j, Y g:i a', strtotime($election['end_date'])); ?>
                                </small>
                                <?php if ($election['status'] === 'active' && !$election['has_voted']): ?>
                                    <a href="vote.php?id=<?php echo $election['id']; ?>" class="btn btn-custom-primary btn-sm">
                                        <i class="fas fa-vote-yea me-1"></i>Vote Now
                                    </a>
                                <?php elseif ($election['has_voted']): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-check me-1"></i>Voted
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <p class="mb-0">No recent elections found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
:root {
    --custom-primary-color: #2c3e50;
    --custom-secondary-color: #3498db;
}

.bg-custom-primary {
    background-color: var(--custom-primary-color) !important;
}

.text-custom-primary {
    color: var(--custom-primary-color) !important;
}

.btn-custom-primary {
    background-color: var(--custom-primary-color);
    border-color: var(--custom-primary-color);
    color: #fff;
}

.btn-custom-primary:hover {
    background-color: rgb(25, 58, 92);
    border-color: rgb(25, 58, 92);
    color: #fff;
}

.card {
    border-radius: 15px;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.list-group-item {
    transition: transform 0.2s;
}

.list-group-item:hover {
    transform: translateY(-2px);
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../includes/footer.php'; ?>
