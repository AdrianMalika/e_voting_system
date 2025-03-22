<?php
require_once '../includes/db.php';

if (isset($_GET['election_id'])) {
    $stmt = $conn->prepare("SELECT branch FROM elections WHERE id = ?");
    $stmt->execute([$_GET['election_id']]);
    $election = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['branch' => $election['branch']]);
} 