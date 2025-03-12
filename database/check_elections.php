<?php
require_once '../config/database.php';

try {
    echo "All elections in database:\n";
    $stmt = $conn->query("
        SELECT id, title, start_date, end_date, status, manual_status, created_at 
        FROM elections 
        ORDER BY created_at DESC
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    // Check for duplicates
    echo "\n\nDuplicate elections (same title and dates):\n";
    $stmt = $conn->query("
        SELECT title, start_date, end_date, COUNT(*) as count
        FROM elections
        GROUP BY title, start_date, end_date
        HAVING COUNT(*) > 1
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
