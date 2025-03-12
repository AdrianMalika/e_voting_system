<?php
require_once '../config/database.php';

try {
    echo "Checking elections table structure:\n";
    $stmt = $conn->query("SHOW CREATE TABLE elections");
    print_r($stmt->fetch());

    echo "\n\nChecking election_positions table structure:\n";
    $stmt = $conn->query("SHOW CREATE TABLE election_positions");
    print_r($stmt->fetch());

    echo "\n\nLast 5 elections:\n";
    $stmt = $conn->query("SELECT * FROM elections ORDER BY id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n\nLast auto_increment value for elections:\n";
    $stmt = $conn->query("SELECT AUTO_INCREMENT 
                         FROM information_schema.TABLES 
                         WHERE TABLE_SCHEMA = 'e_voting_system' 
                         AND TABLE_NAME = 'elections'");
    print_r($stmt->fetch());

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
