<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    die("Unauthorized access");
}

// Get the election ID from URL
$election_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$election_id) {
    die("No election ID provided");
}

echo "<h2>Election Status Check</h2>";

// 1. Direct database query
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Direct Database Query:</h3>";
echo "<pre>";
print_r($election);
echo "</pre>";

// 2. Try updating status directly
$update = $conn->prepare("UPDATE elections SET status = 'active' WHERE id = ?");
$result = $update->execute([$election_id]);

echo "<h3>Update Result:</h3>";
echo "Update successful: " . ($result ? "Yes" : "No") . "<br>";

// 3. Check status after direct update
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$updated = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>After Direct Update:</h3>";
echo "<pre>";
print_r($updated);
echo "</pre>";

// 4. Show all possible values for status
$stmt = $conn->query("SHOW COLUMNS FROM elections LIKE 'status'");
$statusInfo = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Status Column Definition:</h3>";
echo "<pre>";
print_r($statusInfo);
echo "</pre>";
?>
