<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    $_SESSION['error'] = "Invalid election ID";
    header("Location: elections.php");
    exit();
}

// Get election details
$stmt = $conn->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) as total_votes,
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.voter_id = ?) as has_voted
    FROM elections e 
    WHERE e.id = ?
");
$stmt->execute([$_SESSION['user_id'], $election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    $_SESSION['error'] = "Election not found";
    header("Location: elections.php");
    exit();
}

// Pagination setup
$items_per_page = 6;
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
$offset = ($page - 1) * $items_per_page;

// Get candidates for this election
$stmt = $conn->prepare("
    SELECT c.*, 
           c.photo_url AS profile_photo,
           u.name AS full_name,
           (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) as vote_count
    FROM election_candidates ec
    LEFT JOIN candidates c ON ec.candidate_id = c.id
    LEFT JOIN users u ON c.id = u.id
    WHERE ec.election_id = :election_id
    ORDER BY ec.created_at ASC
    LIMIT :limit OFFSET :offset
");

// Bind parameters as named parameters
$stmt->bindValue(':election_id', $election_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

// Execute the query
$stmt->execute();


// Get total candidate count
$stmt = $conn->prepare("SELECT COUNT(*) FROM election_candidates WHERE election_id = ?");
$stmt->execute([$election_id]);
$total_candidates = $stmt->fetchColumn();
$total_pages = ceil($total_candidates / $items_per_page);

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!$candidate_id) {
        $_SESSION['error'] = "Please select a candidate";
    } else if ($election['has_voted'] > 0) {
        $_SESSION['error'] = "You have already voted in this election";
    } else if ($election['status'] !== 'active') {
        $_SESSION['error'] = "This election is not currently active";
    } else {
        try {
            $conn->beginTransaction();
            
            // Record the vote
            $stmt = $conn->prepare("
                INSERT INTO votes (election_id, candidate_id, voter_id, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$election_id, $candidate_id, $_SESSION['user_id']]);
            
            // Add notification
            add_notification(
                $conn,
                'vote_cast',
                "You have successfully cast your vote in the election: " . $election['title'],
                $election_id,
                'election',
                $_SESSION['user_id']
            );
            
            $conn->commit();
            $_SESSION['success'] = "Your vote has been recorded successfully";
            header("Location: view_election.php?id=" . $election_id);
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = "Error recording vote: " . $e->getMessage();
        }
    }
}

// Calculate time remaining or time until start
$now = new DateTime();
$start = new DateTime($election['start_date']);
$end = new DateTime($election['end_date']);

$time_status = '';
if ($now < $start) {
    $interval = $now->diff($start);
    $time_status = "Starts in " . format_interval($interval);
} else if ($now <= $end) {
    $interval = $now->diff($end);
    $time_status = "Ends in " . format_interval($interval);
} else {
    $time_status = "Election ended";
}

// Helper function to format time interval
function format_interval($interval) {
    if ($interval->days > 0) {
        return $interval->format('%d days, %h hours');
    } else if ($interval->h > 0) {
        return $interval->format('%h hours, %i minutes');
    } else {
        return $interval->format('%i minutes');
    }
}
?>

<div class="container py-4">
    <div class="mb-4">
        <a href="elections.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Elections
        </a>
    </div>

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

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title"><?php echo htmlspecialchars($election['title']); ?></h2>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($election['description'])); ?></p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <strong>Start Date:</strong> <?php echo date('M j, Y g:i A', strtotime($election['start_date'])); ?>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        <strong>End Date:</strong> <?php echo date('M j, Y g:i A', strtotime($election['end_date'])); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong>Status:</strong> 
                        <span class="badge bg-<?php echo $election['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($election['status']); ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-hourglass-half text-primary me-2"></i>
                        <strong>Time Status:</strong> <?php echo $time_status; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($candidates)): ?>
        <h3 class="mb-4">Candidates</h3>
        <div class="row">
            <?php foreach ($candidates as $candidate): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if ($candidate['profile_photo']): ?>
                            <img src="<?php echo htmlspecialchars($candidate['profile_photo']); ?>" 
                                 class="card-img-top candidate-photo" 
                                 alt="<?php echo htmlspecialchars($candidate['full_name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top candidate-photo-placeholder">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($candidate['full_name']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($candidate['platform'])); ?></p>
                            
                            <?php if ($election['status'] === 'active' && !$election['has_voted']): ?>
                                <form method="post" class="mt-3">
                                    <input type="hidden" name="vote" value="1">
                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                    <button type="submit" class="btn btn-primary w-100" 
                                            onclick="return confirm('Are you sure you want to vote for <?php echo htmlspecialchars($candidate['full_name']); ?>?')">
                                        <i class="fas fa-vote-yea me-2"></i>Vote
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?id=<?php echo $election_id; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No candidates have been registered for this election yet.
        </div>
    <?php endif; ?>
</div>

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
.candidate-photo-placeholder i {
    font-size: 5rem;
    color: #dee2e6;
}
</style>

<?php require_once '../includes/footer.php'; ?>
