<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to your local timezone
date_default_timezone_set('Africa/Blantyre');

require_once '../includes/header.php'; // This file should already call session_start()

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Please login as an administrator.";
    header("Location: ../login.php");
    exit();
}

// Handle form submissions (for approving/rejecting nominations)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if (isset($_POST['nomination_id'])) {
            $nomination_id = filter_var($_POST['nomination_id'], FILTER_SANITIZE_NUMBER_INT);
            
            switch ($_POST['action']) {
                case 'approve':
                    $stmt = $conn->prepare("
                        UPDATE nominations 
                        SET status = 'approved' 
                        WHERE nomination_id = ?
                    ");
                    $stmt->execute([$nomination_id]);

                    // Log the approval
                    $stmt = $conn->prepare("
                        INSERT INTO audit_logs (user_id, action, details, ip_address)
                        VALUES (?, 'APPROVE_NOMINATION', ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        "Approved nomination ID: $nomination_id",
                        $_SERVER['REMOTE_ADDR']
                    ]);
                    $_SESSION['success'] = "Nomination approved successfully";
                    break;

                case 'reject':
                    $stmt = $conn->prepare("
                        UPDATE nominations 
                        SET status = 'rejected' 
                        WHERE nomination_id = ?
                    ");
                    $stmt->execute([$nomination_id]);

                    // Log the rejection
                    $stmt = $conn->prepare("
                        INSERT INTO audit_logs (user_id, action, details, ip_address)
                        VALUES (?, 'REJECT_NOMINATION', ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        "Rejected nomination ID: $nomination_id",
                        $_SERVER['REMOTE_ADDR']
                    ]);
                    $_SESSION['success'] = "Nomination rejected successfully";
                    break;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: candidates.php");
    exit();
}

// Handle setting application period (only the end date)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_period'])) {
    error_log("Form submitted for setting application period.");

    // Replace "T" with a space so that strtotime can correctly parse the datetime-local value
    $input = str_replace("T", " ", $_POST['application_end']);
    error_log("Original input: " . $_POST['application_end']);
    error_log("Converted input: " . $input);

    $applicationEnd = strtotime($input); // Convert to Unix timestamp
    error_log("Converted Timestamp: " . $applicationEnd);

    if (!$applicationEnd) {
        $_SESSION['error'] = "Invalid date format. Input was: " . htmlspecialchars($_POST['application_end']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Update the application end time in the database
    $stmt = $conn->prepare("INSERT INTO application_period (end) VALUES (?) ON DUPLICATE KEY UPDATE end = VALUES(end)");
    if (!$stmt->execute([$applicationEnd])) {
        $dbError = implode(", ", $stmt->errorInfo());
        error_log("Database Error: " . $dbError);
        $_SESSION['error'] = "Database error: " . $dbError;
    } else {
        $_SESSION['success'] = "Application period updated successfully.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Load nominations for display
$stmt = $conn->prepare("
    SELECT n.*,
           u.name as user_name, 
           u.email as user_email,
           photo.file_path as photo_path,
           trans.file_path as transcript_path
    FROM nominations n
    JOIN users u ON n.user_id = u.id
    LEFT JOIN nomination_documents photo ON n.nomination_id = photo.nomination_id 
        AND photo.document_type = 'photo'
    LEFT JOIN nomination_documents trans ON n.nomination_id = trans.nomination_id 
        AND trans.document_type = 'transcript'
    ORDER BY n.submission_date DESC
");
$stmt->execute();
$nominations = $stmt->fetchAll();

// Load application period from the database (end date only)
$stmt = $conn->prepare("SELECT end FROM application_period ORDER BY id DESC LIMIT 1");
$stmt->execute();
$applicationPeriod = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['end' => 0];
$applicationEnd = $applicationPeriod['end'];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Candidate Applications</h2>
            <p class="text-muted">Review and manage candidate nominations</p>
        </div>
        <!-- Button to trigger modal -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#setPeriodModal">
            <i class="fas fa-clock me-2"></i>Set Application Period
        </button>
    </div>

    <!-- Modal for setting application end date -->
    <div class="modal fade" id="setPeriodModal" tabindex="-1" role="dialog" aria-labelledby="setPeriodModalLabel" aria-hidden="true" aria-modal="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="setPeriodModalLabel">Set Application End Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="application_end" class="form-label">Application End Date</label>
                            <input type="datetime-local" class="form-control" id="application_end" name="application_end" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="set_period" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Countdown Timer -->
    <div id="countdown-timer" class="alert alert-info mt-4"></div>

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
</div>

<!-- Nominations Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Photo</th>
                        <th>Personal Details</th>
                        <th>Academic Info</th>
                        <th>Position Details</th>
                        <th>Documents</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nominations as $nomination): ?>
                        <tr>
                            <td>
                                <?php if (!empty($nomination['photo_path'])): ?>
                                    <img src="/e_voting_system/uploads/profile_photos/<?php echo basename($nomination['photo_path']); ?>" 
                                         alt="Candidate Photo" 
                                         class="rounded-circle"
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='/e_voting_system/assets/images/default-avatar.png';">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($nomination['first_name'] . ' ' . $nomination['surname']); ?></strong><br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($nomination['email']); ?><br>
                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($nomination['phone']); ?>
                                </small>
                            </td>
                            <td>
                                <strong>Student ID:</strong> <?php echo htmlspecialchars($nomination['student_id']); ?><br>
                                <strong>Program:</strong> <?php echo htmlspecialchars($nomination['program']); ?><br>
                                <strong>Year:</strong> <?php echo htmlspecialchars($nomination['year_of_study']); ?><br>
                                <strong>GPA:</strong> <?php echo htmlspecialchars($nomination['gpa']); ?>
                            </td>
                            <td>
                                <strong>Role:</strong> <?php echo htmlspecialchars($nomination['role']); ?><br>
                                <strong>Branch:</strong> <?php echo htmlspecialchars($nomination['branch']); ?>
                            </td>
                            <td>
                                <?php if (!empty($nomination['transcript_path'])): ?>
                                    <a href="/e_voting_system/uploads/academic_transcripts/<?php echo basename($nomination['transcript_path']); ?>" 
                                       class="btn btn-sm btn-outline-primary mb-2" 
                                       target="_blank">
                                        <i class="fas fa-file-alt me-1"></i>View Transcript
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No transcript uploaded</span>
                                <?php endif; ?>

                                <!-- Debug Information (remove in production) -->
                                <?php if (isset($_SESSION['debug']) && $_SESSION['debug']): ?>
                                    <div class="small text-muted mt-2">
                                        <strong>Debug Info:</strong><br>
                                        Photo Path: <?php echo htmlspecialchars($nomination['photo_path']); ?><br>
                                        Transcript Path: <?php echo htmlspecialchars($nomination['transcript_path']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $nomination['status'] ?? 'pending';
                                    $statusClass = [
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'warning'
                                    ][$status] ?? 'warning';
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!isset($nomination['status']) || $nomination['status'] === 'pending'): ?>
                                    <div class="btn-group">
                                        <form method="POST" class="me-1">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="nomination_id" value="<?php echo htmlspecialchars($nomination['nomination_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="nomination_id" value="<?php echo htmlspecialchars($nomination['nomination_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.table th {
    font-weight: 600;
    color: #444;
}
.badge {
    padding: 0.5em 0.75em;
}
.btn-group .btn {
    border-radius: 4px;
}
.btn-group form {
    display: inline-block;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// JavaScript to handle the countdown timer
document.addEventListener('DOMContentLoaded', function() {
    // Convert PHP Unix timestamp (in seconds) to milliseconds
    const applicationEndTimestamp = <?php echo $applicationEnd * 1000; ?>;
    const countdownElement = document.getElementById('countdown-timer');
    
    if (applicationEndTimestamp <= 0) {
        countdownElement.textContent = "Application period is not set.";
        return;
    }
    
    function updateCountdown() {
        const now = new Date().getTime();
        const timeRemaining = applicationEndTimestamp - now;
        
        if (timeRemaining <= 0) {
            countdownElement.textContent = "Application period has ended.";
            countdownElement.classList.remove("alert-info");
            countdownElement.classList.add("alert-danger");
            clearInterval(intervalId);
        } else {
            const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
    
            countdownElement.textContent = `Time remaining: ${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
    }
    
    updateCountdown();
    const intervalId = setInterval(updateCountdown, 1000);
});
</script>

<?php require_once '../includes/footer.php'; ?>
