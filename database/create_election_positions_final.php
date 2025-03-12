<?php
require_once '../config/database.php';

try {
    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Drop the table if it exists
    $conn->exec("DROP TABLE IF EXISTS election_positions");
    
    // Create the table
    $conn->exec("
        CREATE TABLE election_positions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            election_id INT NOT NULL,
            position_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (election_id),
            UNIQUE KEY unique_position_per_election (election_id, position_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Add the foreign key
    $conn->exec("
        ALTER TABLE election_positions 
        ADD CONSTRAINT fk_election_positions_election_id 
        FOREIGN KEY (election_id) REFERENCES elections(id) 
        ON DELETE CASCADE
    ");
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "Election positions table created successfully!\n";
} catch(PDOException $e) {
    // Re-enable foreign key checks even if there's an error
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Error: " . $e->getMessage() . "\n";
}
