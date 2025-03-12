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
$election_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    header("Location: elections.php");
    exit();
}

// Update election statuses
update_election_status($conn);

// Check if election is active and user hasn't voted
$stmt = $conn->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.voter_id = ?) as has_voted
    FROM elections e
    WHERE e.id = ?
");
$stmt->execute([$_SESSION['user_id'], $election_id]);
$election = $stmt->fetch();

// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Current time: " . date('Y-m-d H:i:s') . "\n";
    echo "Election details:\n";
    if ($election) {
        echo "Title: " . $election['title'] . "\n";
        echo "Start: " . $election['start_date'] . "\n";
        echo "End: " . $election['end_date'] . "\n";
        echo "Status: " . $election['status'] . "\n";
        echo "Has Voted: " . ($election['has_voted'] ? 'true' : 'false') . "\n";
    } else {
        echo "No election found\n";
    }
    echo "</pre>";
}

if (!$election) {
    $_SESSION['error'] = "Election not found";
    header("Location: elections.php");
    exit();
}

if ($election['status'] !== 'active') {
    $_SESSION['error'] = "This election is not currently active";
    header("Location: elections.php");
    exit();
}

if ($election['has_voted']) {
    $_SESSION['error'] = "You have already voted in this election";
    header("Location: elections.php");
    exit();
}

// Get candidates for this election with their details
$stmt = $conn->prepare("
    SELECT 
        c.*,
        u.name as candidate_name,
        u.email,
        c.position,
        COALESCE(c.manifesto, '') as manifesto,
        COALESCE(c.photo_url, '') as photo_url
    FROM candidates c
    JOIN users u ON c.user_id = u.id
    JOIN election_candidates ec ON c.id = ec.candidate_id
    WHERE ec.election_id = ? AND c.status = 'approved'
    ORDER BY u.name
");
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();

if (empty($candidates)) {
    $_SESSION['error'] = "No candidates found for this election";
    header("Location: elections.php");
    exit();
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!$candidate_id) {
        $_SESSION['error'] = "Please select a candidate";
    } else {
        try {
            $conn->beginTransaction();
            
            // Record the vote
            $stmt = $conn->prepare("
                INSERT INTO votes (election_id, voter_id, candidate_id)
                VALUES (?, ?, ?)
            ");
            if ($stmt->execute([$election_id, $_SESSION['user_id'], $candidate_id])) {
                // Get candidate and election details for notification
                $stmt = $conn->prepare("
                    SELECT 
                        e.title as election_title,
                        u.name as candidate_name,
                        (SELECT COUNT(*) FROM votes WHERE election_id = ?) as total_votes
                    FROM elections e
                    JOIN election_candidates ec ON e.id = ec.election_id
                    JOIN candidates c ON ec.candidate_id = c.id
                    JOIN users u ON c.user_id = u.id
                    WHERE e.id = ? AND c.id = ?
                ");
                $stmt->execute([$election_id, $election_id, $candidate_id]);
                $details = $stmt->fetch();

                // Add notification
                $message = "New vote cast for " . htmlspecialchars($details['candidate_name']) . 
                          " in election: " . htmlspecialchars($details['election_title']) . 
                          " (Total votes: " . $details['total_votes'] . ")";
                add_notification($conn, 'new_vote', $message, $election_id, 'election');

                $conn->commit();
                $_SESSION['success'] = "Your vote has been recorded successfully!";
            } else {
                throw new Exception("Failed to record vote");
            }
            
            header("Location: elections.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = "Error recording vote: " . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Vote: <?php echo htmlspecialchars($election['title']); ?></h2>
        <a href="elections.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Elections
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" id="voteForm">
                <div class="list-group">
                    <?php foreach ($candidates as $candidate): ?>
                        <label class="list-group-item">
                            <div class="d-flex">
                                <!-- Radio button wrapper with fixed width -->
                                <div class="d-flex align-items-start" style="width: 30px; padding-top: 2px;">
                                    <input class="form-check-input" type="radio" name="candidate_id" 
                                           value="<?php echo $candidate['id']; ?>" required>
                                </div>
                                
                                <div class="d-flex gap-3 flex-grow-1">
                                    <!-- Candidate Photo -->
                                    <div style="width: 100px; height: 100px;" class="flex-shrink-0">
                                        <?php if ($candidate['photo_url']): ?>
                                            <img src="<?php echo '../' . htmlspecialchars($candidate['photo_url']); ?>" 
                                                 class="rounded-circle img-thumbnail"
                                                 style="width: 100px; height: 100px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($candidate['candidate_name']); ?>">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 100px; height: 100px;">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Candidate Information -->
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($candidate['candidate_name']); ?></h5>
                                        <p class="mb-2">
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($candidate['position']); ?>
                                            </span>
                                        </p>
                                        <?php if ($candidate['manifesto']): ?>
                                            <div class="card bg-light">
                                                <div class="card-body py-2">
                                                    <small>
                                                        <strong class="d-block mb-1">Manifesto:</strong>
                                                        <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary" 
                            onclick="return confirm('Are you sure about your vote? This cannot be changed later.');">
                        <i class="fas fa-vote-yea me-2"></i>Submit Vote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
