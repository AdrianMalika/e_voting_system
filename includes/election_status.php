<?php
require_once __DIR__ . '/notifications.php';

function update_election_status($conn) {
    $now = date('Y-m-d H:i:s');
    
    // Get all students
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'student'");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Update elections that should be active (past start date but before end date)
    $stmt = $conn->prepare("
        SELECT id, title 
        FROM elections 
        WHERE manual_status = 0 
        AND start_date <= ? 
        AND end_date > ?
        AND status != 'active'
    ");
    $stmt->execute([$now, $now]);
    $elections_to_activate = $stmt->fetchAll();
    
    if (!empty($elections_to_activate)) {
        $stmt = $conn->prepare("
            UPDATE elections 
            SET status = 'active' 
            WHERE id IN (" . implode(',', array_column($elections_to_activate, 'id')) . ")
        ");
        $stmt->execute();
        
        // Create notifications for elections that just started
        foreach ($elections_to_activate as $election) {
            // Create a general notification for admins
            add_notification(
                $conn,
                'election_started',
                "The election '{$election['title']}' has started",
                $election['id'],
                'election'
            );
            
            // Create notifications for each student
            foreach ($students as $student_id) {
                add_notification(
                    $conn,
                    'election_started',
                    "The election '{$election['title']}' has started and is now open for voting!",
                    $election['id'],
                    'election',
                    $student_id
                );
            }
        }
    }
    
    // Update elections that should be completed (past end date)
    $stmt = $conn->prepare("
        SELECT id, title 
        FROM elections 
        WHERE manual_status = 0 
        AND end_date <= ? 
        AND status != 'completed'
    ");
    $stmt->execute([$now]);
    $elections_to_complete = $stmt->fetchAll();
    
    if (!empty($elections_to_complete)) {
        $stmt = $conn->prepare("
            UPDATE elections 
            SET status = 'completed' 
            WHERE id IN (" . implode(',', array_column($elections_to_complete, 'id')) . ")
        ");
        $stmt->execute();
        
        // Create notifications for elections that just ended
        foreach ($elections_to_complete as $election) {
            // Create a general notification for admins
            add_notification(
                $conn,
                'election_ended',
                "The election '{$election['title']}' has ended",
                $election['id'],
                'election'
            );
            
            // Create notifications for each student
            foreach ($students as $student_id) {
                add_notification(
                    $conn,
                    'election_ended',
                    "The election '{$election['title']}' has ended. View the results now!",
                    $election['id'],
                    'election',
                    $student_id
                );
            }
        }
    }
    
    // Update elections that should be upcoming (before start date)
    $stmt = $conn->prepare("
        UPDATE elections 
        SET status = 'upcoming' 
        WHERE manual_status = 0 
        AND start_date > ? 
        AND status != 'upcoming'
    ");
    $stmt->execute([$now]);
}
