<?php
require_once '../config/database.php';

try {
    echo "Checking users table structure:\n";
    $stmt = $conn->query("SHOW CREATE TABLE users");
    $result = $stmt->fetch();
    print_r($result);

    echo "\nChecking notifications table structure:\n";
    $stmt = $conn->query("SHOW CREATE TABLE notifications");
    $result = $stmt->fetch();
    print_r($result);

    echo "\n\nAll notifications in database:\n";
    $stmt = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n\nUnread notifications count:\n";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
    print_r($stmt->fetch());

    echo "\n\nLast error in error log:\n";
    $stmt = $conn->query("SELECT * FROM error_log ORDER BY created_at DESC LIMIT 1");
    $error = $stmt->fetch();
    if ($error) {
        print_r($error);
    } else {
        echo "No errors found\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
