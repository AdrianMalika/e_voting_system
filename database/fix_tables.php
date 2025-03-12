<?php
require_once '../config/database.php';

try {
    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Drop the foreign key constraint from election_positions
    $conn->exec("ALTER TABLE election_positions DROP FOREIGN KEY fk_election_positions_election_id");
    
    // Convert elections table to InnoDB
    $conn->exec("ALTER TABLE elections ENGINE = InnoDB");
    
    // Add back the foreign key constraint
    $conn->exec("
        ALTER TABLE election_positions 
        ADD CONSTRAINT fk_election_positions_election_id 
        FOREIGN KEY (election_id) REFERENCES elections(id) 
        ON DELETE CASCADE
    ");
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "Tables fixed successfully!\n";
} catch(PDOException $e) {
    // Re-enable foreign key checks even if there's an error
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Error: " . $e->getMessage() . "\n";
}
