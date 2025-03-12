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

echo "<h2>Election Status Monitor</h2>";

// 1. Show current time
echo "<h3>Current Time:</h3>";
echo date('Y-m-d H:i:s') . "<br>";

// 2. Show election details
$stmt = $conn->prepare("
    SELECT *, 
           CASE 
               WHEN NOW() < start_date THEN 'should_be_upcoming'
               WHEN NOW() BETWEEN start_date AND end_date THEN 'should_be_active'
               ELSE 'should_be_completed'
           END as calculated_status
    FROM elections 
    WHERE id = ?
");
$stmt->execute([$election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Election Details:</h3>";
echo "<pre>";
print_r($election);
echo "</pre>";

// 3. Update status and monitor changes
echo "<h3>Status Update Test:</h3>";

// First update
$update = $conn->prepare("UPDATE elections SET status = 'active' WHERE id = ?");
$result = $update->execute([$election_id]);
echo "First update result: " . ($result ? "Success" : "Failed") . "<br>";

// Check immediately after update
$stmt = $conn->prepare("SELECT status FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$status1 = $stmt->fetchColumn();
echo "Status immediately after update: " . $status1 . "<br>";

// Wait 1 second
sleep(1);

// Check again
$stmt->execute([$election_id]);
$status2 = $stmt->fetchColumn();
echo "Status after 1 second: " . $status2 . "<br>";

// 4. Show any relevant triggers
echo "<h3>Triggers on elections table:</h3>";
$triggers = $conn->query("
    SELECT TRIGGER_NAME, ACTION_STATEMENT, EVENT_MANIPULATION
    FROM information_schema.TRIGGERS 
    WHERE EVENT_OBJECT_TABLE = 'elections'
")->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($triggers);
echo "</pre>";

// 5. Show any events
echo "<h3>Events that might affect elections:</h3>";
$events = $conn->query("SHOW EVENTS")->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($events);
echo "</pre>";
?>
