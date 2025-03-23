<?php
require_once '../includes/header.php';
require_once '../includes/election_status.php';
require_once '../includes/db_connect.php'; // Ensure DB connection is included

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($user['role']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

error_log("User ID: " . $_SESSION['user_id']);

try {
    // Get user's branch
    $stmt = $conn->prepare("SELECT branch FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ensure $userBranch is never null
    $userBranch = $result['branch'] ?? 'Unknown';
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $userBranch = 'Unknown';
}
?>

<div class="container-fluid py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center py-4 bg-custom-primary text-white rounded-3 shadow">
            <h1 class="display-4 fw-bold">Student Elections</h1>
            <p class="lead">Choose your representatives and make your voice heard</p>
            <div class="mt-3">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-building me-2"></i>Your Branch: <?php echo htmlspecialchars($userBranch, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Region Cards -->
    <div class="row g-4 justify-content-center">
        <?php
        $branches = ['Blantyre', 'Lilongwe', 'Zomba'];

        foreach ($branches as $branch) {
            $isAvailable = ($userBranch === $branch);
            ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 shadow-lg border-0 region-card <?php echo !$isAvailable ? 'disabled-card' : ''; ?>">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <div class="region-icon mb-3">
                                <i class="fas fa-landmark fa-3x <?php echo $isAvailable ? 'text-custom-primary' : 'text-secondary'; ?>"></i>
                            </div>
                            <h2 class="card-title fw-bold mb-4"><?php echo $branch; ?></h2>
                            <?php if ($isAvailable): ?>
                                <a href="view_elections.php?branch=<?php echo urlencode($branch); ?>" class="btn btn-custom-primary btn-lg w-75 hover-scale">
                                    <i class="fas fa-vote-yea me-2"></i>View Elections
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg w-75" disabled>
                                    <i class="fas fa-lock me-2"></i>Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
 
<!-- Vote Modal -->
<div class="modal fade" id="voteModal" tabindex="-1" aria-labelledby="voteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voteModalLabel">Vote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form content will go here -->
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --custom-primary-color: #2c3e50; 
    --custom-secondary-color: #6c757d; 
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
    background-color: #1f2a38;
    border-color: #1f2a38;
}

.region-card {
    transition: transform 0.3s ease;
    background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
}

.region-card:hover {
    transform: translateY(-5px);
}

.hover-scale {
    transition: transform 0.2s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.region-icon {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    border-radius: 15px;
}

.btn {
    border-radius: 30px;
    padding: 12px 25px;
}

.disabled-card {
    opacity: 0.6;
    cursor: not-allowed;
}

.disabled-card:hover {
    transform: none;
}

.badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
 