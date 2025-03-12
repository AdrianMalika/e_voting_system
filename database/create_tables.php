<?php
require_once '../config/database.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_election_positions.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "Tables created successfully!\n";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
