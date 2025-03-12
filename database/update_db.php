<?php
require_once '../config/database.php';

try {
    // Create election_candidates table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS election_candidates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            election_id INT NOT NULL,
            candidate_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (election_id) REFERENCES elections(id),
            FOREIGN KEY (candidate_id) REFERENCES candidates(id),
            UNIQUE KEY unique_election_candidate (election_id, candidate_id)
        )
    ");
    
    echo "Database updated successfully!";
} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage());
}
?>
