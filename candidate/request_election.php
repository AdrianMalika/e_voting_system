<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

// Ensure user is a candidate
if (!$user || $user['role'] !== 'candidate') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election_id = filter_input(INPUT_POST, 'election_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!$election_id) {
        $_SESSION['error'] = "Invalid election selected";
        header("Location: view_elections.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Get candidate ID
        $stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $candidate_id = $stmt->fetchColumn();

        if (!$candidate_id) {
            throw new Exception("Candidate profile not found");
        }

        // Check if already requested
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM election_candidates 
            WHERE election_id = ? AND candidate_id = ?
        ");
        $stmt->execute([$election_id, $candidate_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("You have already requested to join this election");
        }

        // Add election request
        $stmt = $conn->prepare("
            INSERT INTO election_candidates (election_id, candidate_id)
            VALUES (?, ?)
        ");
        
        if ($stmt->execute([$election_id, $candidate_id])) {
            // Get election details for notification
            $stmt = $conn->prepare("
                SELECT e.title, u.name 
                FROM elections e
                JOIN users u ON u.id = ?
                WHERE e.id = ?
            ");
            $stmt->execute([$user['id'], $election_id]);
            $details = $stmt->fetch();

            // Add notification
            $message = "Candidate " . htmlspecialchars($details['name']) . 
                      " has requested to join election: " . htmlspecialchars($details['title']);
            add_notification($conn, 'election_request', $message, $election_id, 'election');

            $conn->commit();
            $_SESSION['success'] = "Successfully requested to join the election";
        } else {
            throw new Exception("Failed to submit request");
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: view_elections.php");
    exit();
}

// If not POST request, redirect to elections page
header("Location: view_elections.php");
exit();
