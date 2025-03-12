<?php
require_once '../config/database.php';

try {
    // Add user_id column to notifications table
    $conn->exec("
        ALTER TABLE notifications 
        ADD COLUMN user_id INT NULL
    ");

    // Update enum type to include election_started
    $conn->exec("
        ALTER TABLE notifications 
        MODIFY COLUMN type ENUM(
            'new_candidate',
            'election_request',
            'new_vote',
            'election_ended',
            'new_election',
            'election_started'
        ) NOT NULL
    ");

    echo "Successfully updated notifications table structure\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
