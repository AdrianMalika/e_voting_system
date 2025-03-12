<?php
require_once '../config/database.php';

try {
    // Start transaction
    $conn->beginTransaction();

    // Find duplicate elections
    $stmt = $conn->query("
        SELECT GROUP_CONCAT(id) as ids, title, start_date, end_date, COUNT(*) as count
        FROM elections
        GROUP BY title, start_date, end_date
        HAVING COUNT(*) > 1
    ");
    
    while ($row = $stmt->fetch()) {
        $ids = explode(',', $row['ids']);
        // Keep the first one (lowest ID) and delete others
        $keep_id = min($ids);
        $delete_ids = array_diff($ids, [$keep_id]);
        
        if (!empty($delete_ids)) {
            // Delete duplicate elections
            $delete_sql = "DELETE FROM elections WHERE id IN (" . implode(',', $delete_ids) . ")";
            $conn->exec($delete_sql);
            echo "Deleted duplicate elections with IDs: " . implode(', ', $delete_ids) . " for election '{$row['title']}'\n";
        }
    }

    // Add unique constraint to prevent future duplicates
    $conn->exec("
        ALTER TABLE elections 
        ADD CONSTRAINT unique_election 
        UNIQUE (title, start_date, end_date)
    ");

    $conn->commit();
    echo "Successfully fixed duplicate elections and added constraint\n";
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
