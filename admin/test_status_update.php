<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    die("Unauthorized access");
}

$election_id = 5; // The election ID we're testing
$new_status = 'active'; // The status we want to set

echo "<h2>Testing Status Update</h2>";

// 1. Show current election status
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$before = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Before Update:</h3>";
echo "<pre>";
print_r($before);
echo "</pre>";

// 2. Try to update status directly
$stmt = $conn->prepare("UPDATE elections SET status = ?, updated_at = NOW() WHERE id = ?");
$result = $stmt->execute([$new_status, $election_id]);

echo "<h3>Update Result:</h3>";
echo "Success: " . ($result ? "Yes" : "No") . "<br>";
if (!$result) {
    echo "Error: ";
    print_r($stmt->errorInfo());
    echo "<br>";
}

// 3. Check status after update
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$after = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>After Update:</h3>";
echo "<pre>";
print_r($after);
echo "</pre>";

// 4. Show column definition
$result = $conn->query("SHOW COLUMNS FROM elections WHERE Field = 'status'")->fetch();
echo "<h3>Status Column Definition:</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

// 5. Show any triggers
$triggers = $conn->query("
    SELECT TRIGGER_NAME, ACTION_STATEMENT, EVENT_MANIPULATION, EVENT_OBJECT_TABLE
    FROM information_schema.TRIGGERS 
    WHERE EVENT_OBJECT_TABLE = 'elections'
")->fetchAll();

echo "<h3>Triggers:</h3>";
echo "<pre>";
print_r($triggers);
echo "</pre>";
?>
