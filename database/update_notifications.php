<?php
require_once '../config/database.php';

try {
    // Create notifications table
    $sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        type ENUM('new_candidate', 'election_request', 'new_vote', 'election_ended') NOT NULL,
        message TEXT NOT NULL,
        reference_id INT,
        reference_type VARCHAR(50),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (type),
        INDEX (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->exec($sql);
    echo "Notifications table created successfully\n";

} catch(PDOException $e) {
    echo "Error creating notifications table: " . $e->getMessage() . "\n";
}
