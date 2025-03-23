<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!$user || $user['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'election_id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    header("Location: elections.php");
    exit();
}

// Current time for comparison
$now = '2025-01-07 16:48:34';

// Get election details - allow adding candidates if election hasn't started yet
$stmt = $conn->prepare("
    SELECT * FROM elections 
    WHERE id = ? AND start_date > ?
");
$stmt->execute([$election_id, $now]);
$election = $stmt->fetch();

if (!$election) {
    // Check if election exists at all
    $stmt = $conn->prepare("SELECT title, start_date FROM elections WHERE id = ?");
    $stmt->execute([$election_id]);
    $check_election = $stmt->fetch();
    
    if (!$check_election) {
        $_SESSION['error'] = "Election not found";
    } else {
        $_SESSION['error'] = "Cannot add candidates - election starts at " . date('Y-m-d H:i', strtotime($check_election['start_date']));
    }
    header("Location: elections.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if ($action === 'add') {
        $candidate_ids = $_POST['candidate_ids'] ?? [];
        
        if (empty($candidate_ids)) {
            $_SESSION['error'] = "Please select at least one candidate";
        } else {
            try {
                $conn->beginTransaction();
                
                // Prepare statement for inserting candidates
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO election_candidates (election_id, candidate_id)
                    VALUES (?, ?)
                ");
                
                $added_count = 0;
                foreach ($candidate_ids as $candidate_id) {
                    $stmt->execute([$election_id, $candidate_id]);
                    if ($stmt->rowCount() > 0) {
                        $added_count++;
                    }
                }
                
                $conn->commit();
                $_SESSION['success'] = "Added $added_count candidate(s) to the election";
                
            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['error'] = "Error adding candidates: " . $e->getMessage();
            }
        }
    } elseif ($action === 'remove') {
        $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_SANITIZE_NUMBER_INT);
        
        if ($candidate_id) {
            try {
                $stmt = $conn->prepare("
                    DELETE FROM election_candidates 
                    WHERE election_id = ? AND candidate_id = ?
                ");
                $stmt->execute([$election_id, $candidate_id]);
                
                $_SESSION['success'] = "Candidate removed from election";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error removing candidate: " . $e->getMessage();
            }
        }
    }
    
    // Redirect to refresh the page
    header("Location: manage_election_candidates.php?election_id=" . $election_id);
    exit();
}

// Get current candidates in this election
$stmt = $conn->prepare("
    SELECT c.*, u.name as user_name, u.email 
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    JOIN election_candidates ec ON c.id = ec.candidate_id
    WHERE ec.election_id = ?
");
$stmt->execute([$election_id]);
$current_candidates = $stmt->fetchAll();

// Get available candidates (not yet in this election)
$stmt = $conn->prepare("
    SELECT c.*, u.name as user_name, u.email 
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    WHERE c.id NOT IN (
        SELECT candidate_id 
        FROM election_candidates 
        WHERE election_id = ?
    )
");
$stmt->execute([$election_id]);
$available_candidates = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Candidates for <?php echo htmlspecialchars($election['title']); ?></h2>
        <a href="elections.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Elections
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Current Candidates -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Current Candidates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($current_candidates)): ?>
                        <p class="text-muted mb-0">No candidates assigned to this election yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($current_candidates as $candidate): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($candidate['user_name']); ?></h6>
                                        <small class="text-muted">
                                            Email: <?php echo htmlspecialchars($candidate['email']); ?>
                                        </small>
                                    </div>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this candidate?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Available Candidates -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Available Candidates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($available_candidates)): ?>
                        <p class="text-muted mb-0">No available candidates to add.</p>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="list-group mb-3">
                                <?php foreach ($available_candidates as $candidate): ?>
                                    <label class="list-group-item">
                                        <input class="form-check-input me-1" type="checkbox" name="candidate_ids[]" value="<?php echo $candidate['id']; ?>">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($candidate['user_name']); ?></h6>
                                            <small class="text-muted">
                                                Email: <?php echo htmlspecialchars($candidate['email']); ?>
                                            </small>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Add Selected Candidates
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
