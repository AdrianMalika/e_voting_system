<?php
require_once '../config/database.php';

try {
    echo "All notifications in database:\n";
    $stmt = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\nChecking if add_notification function is working:\n";
    require_once '../includes/notifications.php';
    $result = add_notification(
        $conn, 
        'election_request', 
        'Test notification', 
        1, 
        'election'
    );
    echo "Test notification added: " . ($result ? "Yes" : "No") . "\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
