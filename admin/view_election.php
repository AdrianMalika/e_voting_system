<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'election_id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    $_SESSION['error'] = "Invalid election ID";
    header("Location: manage_elections.php");
    exit();
}

// Get election details
$stmt = $conn->prepare("
    SELECT * FROM elections WHERE id = ?
");
$stmt->execute([$election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    $_SESSION['error'] = "Election not found";
    header("Location: manage_elections.php");
    exit();
}

// Get all approved nominees for this election
$stmt = $conn->prepare("
    SELECT 
        n.nomination_id,
        n.first_name,
        n.surname,
        n.email,
        n.phone,
        n.student_id,
        n.program,
        n.year_of_study,
        n.role,
        n.branch,
        n.photo_path,
        n.manifesto,
        n.submission_date
    FROM nominations n
    WHERE n.election_id = ?
    AND n.status = 'approved'
    AND n.branch = (SELECT branch FROM elections WHERE id = ?)
    ORDER BY n.role, n.first_name
");
$stmt->execute([$election_id, $election_id]);
$nominees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <!-- Enhanced Header -->
   <!-- Enhanced Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white shadow-lg rounded-4 border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="display-6 mb-2"><?php echo htmlspecialchars($election['title']); ?></h2>
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building me-2"></i>
                                <span><?php echo htmlspecialchars($election['branch']); ?> Branch</span>
    </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users me-2"></i>
                                <span><?php echo count($nominees); ?> Candidates</span>
        </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar me-2"></i>
                                <span><?php echo date('M j, Y', strtotime($election['start_date'])); ?> - <?php echo date('M j, Y', strtotime($election['end_date'])); ?></span>
        </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2"></i>
                                <span><?php echo ucfirst($election['status']); ?></span>
                    </div>
                </div>
                    </div>
                    <a href="manage_elections.php" class="btn btn-light btn-lg rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back to Elections
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Candidates Section -->
    <?php if (!empty($nominees)): ?>
        <?php 
        $current_role = '';
        $positions = [];
        foreach ($nominees as $nominee) {
            if (!isset($positions[$nominee['role']])) {
                $positions[$nominee['role']] = [];
            }
            $positions[$nominee['role']][] = $nominee;
        }
        ?>

        <!-- Position Tabs -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <ul class="nav nav-pills mb-4" id="positionTabs" role="tablist">
                    <?php $first = true; foreach ($positions as $role => $roleNominees): ?>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                id="<?php echo sanitize_id($role); ?>-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#<?php echo sanitize_id($role); ?>"
                                type="button"
                                role="tab">
                            <?php echo htmlspecialchars($role); ?> 
                            <span class="badge bg-primary ms-2"><?php echo count($roleNominees); ?></span>
                        </button>
                    </li>
                    <?php $first = false; endforeach; ?>
                </ul>

                <div class="tab-content" id="positionTabContent">
                    <?php $first = true; foreach ($positions as $role => $roleNominees): ?>
                    <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                         id="<?php echo sanitize_id($role); ?>" 
                         role="tabpanel">
                        <div class="row g-4">
                            <?php foreach ($roleNominees as $nominee): ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm hover-shadow">
                                    <div class="position-relative">
                                        <?php if ($nominee['photo_path']): ?>
                                            <img src="<?php echo htmlspecialchars($nominee['photo_path']); ?>" 
                                 class="card-img-top candidate-photo" 
                                                 alt="<?php echo htmlspecialchars($nominee['first_name'] . ' ' . $nominee['surname']); ?>">
                        <?php else: ?>
                            <div class="card-img-top candidate-photo-placeholder">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        <?php endif; ?>
                                        <div class="position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo htmlspecialchars($nominee['branch']); ?>
                                            </span>
                                        </div>
                                    </div>
                        <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            <?php echo htmlspecialchars($nominee['first_name'] . ' ' . $nominee['surname']); ?>
                                        </h5>
                                        <div class="d-flex flex-column gap-2 mb-3">
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-graduation-cap me-2"></i>
                                                <span><?php echo htmlspecialchars($nominee['program']); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-envelope me-2"></i>
                                                <span><?php echo htmlspecialchars($nominee['email']); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-phone me-2"></i>
                                                <span><?php echo htmlspecialchars($nominee['phone']); ?></span>
                                            </div>
                                        </div>
                                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" 
                                                data-bs-target="#manifesto-<?php echo $nominee['nomination_id']; ?>">
                                            <i class="fas fa-file-alt me-2"></i>View Manifesto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $first = false; endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Manifesto Modals -->
        <?php foreach ($nominees as $nominee): ?>
        <div class="modal fade" id="manifesto-<?php echo $nominee['nomination_id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title">Candidate Manifesto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <?php if ($nominee['photo_path']): ?>
                                <img src="<?php echo htmlspecialchars($nominee['photo_path']); ?>" 
                                     class="rounded-circle me-3" 
                                     style="width: 64px; height: 64px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($nominee['first_name'] . ' ' . $nominee['surname']); ?>">
                            <?php endif; ?>
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($nominee['first_name'] . ' ' . $nominee['surname']); ?></h4>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($nominee['role']); ?></p>
                            </div>
                        </div>
                        <div class="bg-light p-4 rounded-3">
                            <?php echo nl2br(htmlspecialchars($nominee['manifesto'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="alert alert-info shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <h4 class="alert-heading mb-1">No Candidates Yet</h4>
                    <p class="mb-0">No approved candidates found for this election.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Helper function to create valid IDs for tabs
function sanitize_id($string) {
    return preg_replace('/[^a-z0-9]/i', '_', strtolower($string));
}
?>

<style>
:root {
    --primary-color: #2c3e50;
    --primary-light: #3498db;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
}

.card {
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}

.candidate-photo {
    height: 250px;
    object-fit: cover;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}

.candidate-photo-placeholder {
    height: 250px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}

.nav-pills .nav-link {
    border-radius: 2rem;
    padding: 0.75rem 1.5rem;
    color: var(--primary-color);
}

.nav-pills .nav-link.active {
    background-color: var(--primary-color);
}

.icon-box {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.modal-content {
    border-radius: 1rem;
}

.modal-header {
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}

@media (max-width: 768px) {
    .display-6 {
        font-size: 1.5rem;
    }
    .lead {
        font-size: 1rem;
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
