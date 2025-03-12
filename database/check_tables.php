<?php
require_once '../config/database.php';

try {
    // Check if elections table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'elections'");
    $exists = $stmt->rowCount() > 0;
    echo "Elections table exists: " . ($exists ? "Yes" : "No") . "\n";

    if ($exists) {
        // Show table structure
        $stmt = $conn->query("DESCRIBE elections");
        echo "\nElections table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
