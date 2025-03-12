<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

// Ensure user is admin
if (!$user || $user['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Mark notifications as read if requested
if (isset($_POST['mark_read']) && isset($_POST['notification_ids'])) {
    mark_notifications_read($conn, $_POST['notification_ids']);
}

// Get all notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute();
$notifications = $stmt->fetchAll();

// Clear all notifications if requested
if (isset($_POST['clear_notifications'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM notifications");
        $stmt->execute();
        $_SESSION['success'] = "All notifications have been cleared.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error clearing notifications: " . $e->getMessage();
    }

    header("Location: notifications.php");
    exit();
}

?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Notifications</h2>
        <div>
            <?php if (count($notifications) > 0): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="mark_read" value="1">
                    <?php foreach ($notifications as $notification): ?>
                        <?php if (!$notification['is_read']): ?>
                            <input type="hidden" name="notification_ids[]" value="<?php echo $notification['id']; ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-check-double me-2"></i>Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
            <form method="POST" class="d-inline">
                <input type="hidden" name="clear_notifications" value="1">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Clear All Notifications
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No notifications to display.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?php echo !$notification['is_read'] ? 'list-group-item-primary' : ''; ?>">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <?php
                            $icon = 'bell';
                            switch($notification['type']) {
                                case 'new_candidate':
                                    $icon = 'user-plus';
                                    break;
                                case 'election_request':
                                    $icon = 'vote-yea';
                                    break;
                                case 'new_vote':
                                    $icon = 'check-circle';
                                    break;
                                case 'election_ended':
                                    $icon = 'flag-checkered';
                                    break;
                                case 'new_election':
                                    $icon = 'plus-circle';
                                    break;
                            }
                            ?>
                            <i class="fas fa-<?php echo $icon; ?> me-2"></i>
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <?php if (!$notification['is_read']): ?>
                                <span class="badge bg-primary ms-2">New</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                        </small>
                    </div>
                    <?php if ($notification['reference_id']): ?>
                        <div class="mt-2">
                            <?php
                            switch($notification['type']) {
                                case 'election_request':
                                    echo '<a href="manage_elections.php?view_requests=' . $notification['reference_id'] . '" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>View Request
                                    </a>';
                                    break;
                                case 'new_election':
                                case 'election_ended':
                                    echo '<a href="manage_elections.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-poll me-1"></i>View Elections
                                    </a>';
                                    break;
                                case 'new_vote':
                                    echo '<a href="view_results.php?id=' . $notification['reference_id'] . '" class="btn btn-sm btn-primary">
                                        <i class="fas fa-chart-bar me-1"></i>View Results
                                    </a>';
                                    break;
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
