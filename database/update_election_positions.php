<?php
require_once '../config/database.php';

try {
    // Create election_positions table
    $sql = "
    CREATE TABLE IF NOT EXISTS election_positions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        election_id INT NOT NULL,
        position_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
        UNIQUE KEY unique_position_per_election (election_id, position_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->exec($sql);
    echo "Election positions table created successfully\n";

} catch(PDOException $e) {
    echo "Error creating election positions table: " . $e->getMessage() . "\n";
}
