<?php
require_once '../includes/header.php';
require_once '../includes/election_status.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get user's branch
$stmt = $conn->prepare("SELECT branch FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userBranch = $stmt->fetch(PDO::FETCH_ASSOC)['branch'];

?>

<div class="container-fluid py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center py-4 bg-custom-primary text-white rounded-3 shadow">
            <h1 class="display-4 fw-bold">Student Elections</h1>
            <p class="lead">Choose your representatives and make your voice heard</p>
            <div class="mt-3">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-building me-2"></i>Your Branch: <?php echo htmlspecialchars($userBranch); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Region Cards -->
    <div class="row g-4 justify-content-center">
        <!-- Blantyre Card -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-lg border-0 region-card <?php echo $userBranch !== 'Blantyre' ? 'disabled-card' : ''; ?>">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="region-icon mb-3">
                            <i class="fas fa-landmark fa-3x <?php echo $userBranch === 'Blantyre' ? 'text-custom-primary' : 'text-secondary'; ?>"></i>
                        </div>
                        <h2 class="card-title fw-bold mb-4">Blantyre</h2>
                        <?php if ($userBranch === 'Blantyre'): ?>
                            <a href="view_elections.php?branch=Blantyre" class="btn btn-custom-primary btn-lg w-75 hover-scale">
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

        <!-- Lilongwe Card -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-lg border-0 region-card <?php echo $userBranch !== 'Lilongwe' ? 'disabled-card' : ''; ?>">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="region-icon mb-3">
                            <i class="fas fa-landmark fa-3x <?php echo $userBranch === 'Lilongwe' ? 'text-custom-primary' : 'text-secondary'; ?>"></i>
                        </div>
                        <h2 class="card-title fw-bold mb-4">Lilongwe</h2>
                        <?php if ($userBranch === 'Lilongwe'): ?>
                            <a href="view_elections.php?branch=Lilongwe" class="btn btn-custom-primary btn-lg w-75 hover-scale">
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

        <!-- Zomba Card -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-lg border-0 region-card <?php echo $userBranch !== 'Zomba' ? 'disabled-card' : ''; ?>">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="region-icon mb-3">
                            <i class="fas fa-landmark fa-3x <?php echo $userBranch === 'Zomba' ? 'text-custom-primary' : 'text-secondary'; ?>"></i>
                        </div>
                        <h2 class="card-title fw-bold mb-4">Zomba</h2>
                        <?php if ($userBranch === 'Zomba'): ?>
                            <a href="view_elections.php?branch=Zomba" class="btn btn-custom-primary btn-lg w-75 hover-scale">
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
    background-color: darken(var(--custom-primary-color), 10%);
    border-color: darken(var(--custom-primary-color), 10%);
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
