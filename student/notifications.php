<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';


if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get notifications for current user
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unread count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetchColumn();

// Mark notifications as read if requested
if (isset($_POST['mark_read']) && !empty($_POST['notification_ids'])) {
    $notification_ids = array_map('intval', $_POST['notification_ids']);
    $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id IN ($placeholders) AND user_id = ?
    ");
    $stmt->execute([...$notification_ids, $_SESSION['user_id']]);
    header("Location: notifications.php");
    exit();
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-bell me-2"></i>My Notifications
            <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger ms-2"><?php echo $unread_count; ?> New</span>
            <?php endif; ?>
        </h2>
        <div>
            <?php if (!empty($notifications)): ?>
                <form method="post" class="d-inline">
                    <input type="hidden" name="mark_read" value="1">
                    <?php foreach ($notifications as $notification): ?>
                        <?php if (!$notification['is_read']): ?>
                            <input type="hidden" name="notification_ids[]" value="<?php echo $notification['id']; ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-check-double me-2"></i>Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
            <a href="elections.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Elections
            </a>
        </div>
    </div>

    <?php if (!empty($notifications)): ?>
        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?php echo !$notification['is_read'] ? 'bg-light' : ''; ?>">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <?php if (!$notification['is_read']): ?>
                                <span class="badge bg-danger me-2">New</span>
                            <?php endif; ?>
                            
                            <?php
                            $icon = '';
                            switch ($notification['type']) {
                                case 'election_started':
                                    $icon = 'fa-play-circle text-success';
                                    break;
                                case 'election_ended':
                                    $icon = 'fa-stop-circle text-danger';
                                    break;
                                case 'new_election':
                                    $icon = 'fa-calendar-plus text-info';
                                    break;
                                case 'new_candidate':
                                    $icon = 'fa-user-plus text-info';
                                    break;
                                default:
                                    $icon = 'fa-bell text-secondary';
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?> me-2"></i>
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                        <small class="text-muted">
                            <?php echo date('M j, g:i a', strtotime($notification['created_at'])); ?>
                        </small>
                    </div>
                    <?php if ($notification['reference_id'] && $notification['type'] === 'new_election'): ?>
                        <div class="mt-2">
                            <a href="view_election.php?id=<?php echo $notification['reference_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-vote-yea me-1"></i>View Election Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No notifications to display.
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
