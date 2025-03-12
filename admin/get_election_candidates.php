<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['election_id'])) {
    $stmt = $conn->prepare("
        SELECT candidate_id 
        FROM election_candidates 
        WHERE election_id = ?
    ");
    $stmt->execute([$_GET['election_id']]);
    $candidates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($candidates);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Election ID required']);
}
