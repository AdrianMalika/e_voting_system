<?php
require_once '../config/database.php';

try {
    // Modify the notifications table to include new types
    $conn->exec("
        ALTER TABLE notifications 
        MODIFY COLUMN type ENUM(
            'new_candidate', 
            'election_request', 
            'new_vote', 
            'election_ended',
            'new_election'
        ) NOT NULL
    ");
    
    echo "Notification types updated successfully!\n";
} catch(PDOException $e) {
    echo "Error updating notification types: " . $e->getMessage() . "\n";
}
