<?php

/**
 * Add a new notification to the system
 * 
 * @param PDO $conn Database connection
 * @param string $type Notification type ('new_candidate', 'election_request', 'new_vote', 'election_ended', 'election_started')
 * @param string $message Notification message
 * @param int|null $reference_id ID of the referenced item (optional)
 * @param string|null $reference_type Type of the referenced item (optional)
 * @param int|null $user_id ID of the user to receive the notification (optional)
 * @return bool True if notification was added successfully
 */
if (!function_exists('add_notification')) {
    function add_notification($conn, $type, $message, $reference_id = null, $reference_type = null, $user_id = null) {
        try {
            error_log("Adding notification for user_id: " . ($user_id ?? 'NULL'));
            $stmt = $conn->prepare("
                INSERT INTO notifications (type, message, reference_id, reference_type, user_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$type, $message, $reference_id, $reference_type, $user_id]);
        } catch (PDOException $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Get unread notifications count for a specific user
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
if (!function_exists('get_unread_notifications_count')) {
    function get_unread_notifications_count($conn, $user_id = null) {
        try {
            global $user;
            $sql = "SELECT COUNT(*) FROM notifications WHERE is_read = FALSE";
            $params = [];
            
            if ($user_id !== null) {
                // For admin, show all notifications
                if ($user['role'] === 'admin') {
                    $sql .= " AND (user_id = ? OR user_id IS NULL)";
                } else {
                    // For other roles, only show their specific notifications
                    $sql .= " AND user_id = ?";
                }
                $params[] = $user_id;
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting unread notifications count: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Get notifications for a specific user
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notifications
 */
if (!function_exists('get_user_notifications')) {
    function get_user_notifications($conn, $user_id, $limit = 50) {
        try {
            global $user;
            
            // For debugging
            error_log("Getting notifications for user: " . $user_id . " with role: " . $user['role']);
            
            // Different queries based on user role
            if ($user['role'] === 'admin') {
                // Admin sees all notifications
                $sql = "
                    SELECT * FROM notifications 
                    WHERE user_id = ? OR user_id IS NULL
                    ORDER BY created_at DESC 
                    LIMIT ?
                ";
            } else {
                // Other roles only see their specific notifications
                $sql = "
                    SELECT * FROM notifications 
                    WHERE user_id = ?
                    ORDER BY created_at DESC 
                    LIMIT ?
                ";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $limit]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // For debugging
            error_log("Found " . count($notifications) . " notifications");
            foreach ($notifications as $notification) {
                error_log("Notification: " . json_encode($notification));
            }
            
            return $notifications;
        } catch (PDOException $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Mark notifications as read
 * 
 * @param PDO $conn Database connection
 * @param array $notification_ids Array of notification IDs to mark as read
 * @return bool True if notifications were marked as read successfully
 */
if (!function_exists('mark_notifications_read')) {
    function mark_notifications_read($conn, $notification_ids) {
        try {
            $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE id IN ($placeholders)
            ");
            return $stmt->execute($notification_ids);
        } catch (PDOException $e) {
            error_log("Error marking notifications as read: " . $e->getMessage());
            return false;
        }
    }
}
