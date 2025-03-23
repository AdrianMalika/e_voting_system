<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';
require_once '../includes/election_status.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get election ID
$election_id = $_GET['election_id'] ?? 0;

// Verify election exists and user can access it
$stmt = $conn->prepare("
    SELECT e.*, u.branch as user_branch 
    FROM elections e, users u 
    WHERE e.id = ? AND u.id = ? AND e.branch = u.branch AND e.status = 'active'
");
$stmt->execute([$election_id, $_SESSION['user_id']]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    $_SESSION['error'] = "Invalid election or unauthorized access.";
    header("Location: elections.php");
    exit();
}

// Check if user has already voted
$stmt = $conn->prepare("
    SELECT DISTINCT position_name 
    FROM votes 
    WHERE election_id = ? AND voter_id = ?
");
$stmt->execute([$election_id, $_SESSION['user_id']]);
$votedPositions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get candidates grouped by position
$stmt = $conn->prepare("
    SELECT 
        n.nomination_id,
        n.first_name,
        n.surname,
        n.photo_path,
        n.manifesto,
        n.role as position_name,
        n.branch
    FROM nominations n
    WHERE n.election_id = ? 
    AND n.status = 'approved'
    AND n.branch = ?
    ORDER BY n.role, n.first_name
");
$stmt->execute([$election_id, $election['branch']]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
$positions = [];
foreach ($candidates as $candidate) {
    $positions[$candidate['position_name']][] = $candidate;
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Validate one vote per position
        foreach ($_POST['votes'] as $position => $candidate_id) {
            if (in_array($position, $votedPositions)) {
                throw new Exception("You have already voted for $position");
            }
            
            $stmt = $conn->prepare("
                INSERT INTO votes (election_id, position_name, candidate_id, voter_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$election_id, $position, $candidate_id, $_SESSION['user_id']]);
        }
        
        $conn->commit();
        $_SESSION['success'] = "Your votes have been recorded successfully!";
        header("Location: view_elections.php?branch=" . $election['branch']);
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<div class="container py-6">    
    <!-- Election Header -->
    <div class="card mb-4" style="background-color: #2c3e50; color: white;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($election['title']); ?></h2>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($election['branch']); ?> Branch
                    </p>
                </div>
                <a href="view_elections.php?branch=<?php echo urlencode($election['branch']); ?>" 
                   class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="voteForm">
        <?php foreach ($positions as $position => $position_candidates): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h3 class="mb-0">
                        <?php echo htmlspecialchars($position); ?>
                        <?php if (in_array($position, $votedPositions)): ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check me-1"></i>Voted
                            </span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <?php foreach ($position_candidates as $candidate): ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <?php if ($candidate['photo_path']): ?>
                                            <img src="<?php echo htmlspecialchars($candidate['photo_path']); ?>" 
                                                 class="card-img-top candidate-photo" 
                                                 alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>">
                                        <?php else: ?>
                                            <div class="card-img-top candidate-photo-placeholder">
                                                <i class="fas fa-user-circle fa-4x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>
                                        </h5>
                                        <button type="button" class="btn btn-link p-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#manifesto-<?php echo $candidate['nomination_id']; ?>">
                                            View Manifesto
                                        </button>
                                        
                                        <?php if (!in_array($position, $votedPositions)): ?>
                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="radio" 
                                                       name="votes[<?php echo htmlspecialchars($position); ?>]" 
                                                       value="<?php echo $candidate['nomination_id']; ?>" 
                                                       id="candidate-<?php echo $candidate['nomination_id']; ?>"
                                                       required>
                                                <label class="form-check-label" 
                                                       for="candidate-<?php echo $candidate['nomination_id']; ?>">
                                                    Select Candidate
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!empty(array_diff(array_keys($positions), $votedPositions))): ?>
            <div class="d-grid gap-2">
                <button type="submit" class="btn" style="background-color: #2c3e50; color: white;">
                    <i class="fas fa-vote-yea me-2"></i>Submit Votes
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle me-2"></i>
                You have voted for all positions in this election.
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Manifesto Modals -->
<?php foreach ($candidates as $candidate): ?>
    <div class="modal fade" id="manifesto-<?php echo $candidate['nomination_id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>'s Manifesto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
.candidate-photo {
    height: 200px;
    object-fit: cover;
}

.candidate-photo-placeholder {
    height: 200px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}
</style>

<script>
document.getElementById('voteForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to submit your votes? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>