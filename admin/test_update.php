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

echo "<h2>Testing Election Update</h2>";

// 1. Show current status
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Current Election Status:</h3>";
echo "<pre>";
print_r($election);
echo "</pre>";

// 2. Try direct update
$update = $conn->prepare("UPDATE elections SET status = 'active' WHERE id = ?");
$result = $update->execute([$election_id]);

echo "<h3>Update Result:</h3>";
echo "Update successful: " . ($result ? "Yes" : "No") . "<br>";
if (!$result) {
    echo "Error: ";
    print_r($update->errorInfo());
}

// 3. Check status after update
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$updated = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Updated Election Status:</h3>";
echo "<pre>";
print_r($updated);
echo "</pre>";

// 4. Show table structure
echo "<h3>Table Structure:</h3>";
$structure = $conn->query("SHOW CREATE TABLE elections");
$tableInfo = $structure->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($tableInfo);
echo "</pre>";
?>
