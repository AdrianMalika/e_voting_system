<?php
require_once '../config/database.php';

try {
    // Create the table without foreign key first
    $conn->exec("
        CREATE TABLE IF NOT EXISTS election_positions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            election_id INT NOT NULL,
            position_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Add the unique key
    $conn->exec("
        ALTER TABLE election_positions 
        ADD UNIQUE KEY unique_position_per_election (election_id, position_name)
    ");
    
    // Add the foreign key
    $conn->exec("
        ALTER TABLE election_positions 
        ADD CONSTRAINT fk_election_positions_election_id 
        FOREIGN KEY (election_id) REFERENCES elections(id) 
        ON DELETE CASCADE
    ");
    
    echo "Election positions table created successfully!\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
