<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
//i have modified it

$stmt = $conn->prepare("
    SELECT e.*, 
           COUNT(DISTINCT v.id) as total_votes,
           COUNT(DISTINCT ec.candidate_id) as total_candidates
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    LEFT JOIN election_candidates ec ON e.id = ec.election_id
    WHERE e.status = 'active'
    GROUP BY e.id
    ORDER BY e.start_date ASC
");
$stmt->execute();
$active_elections = $stmt->fetchAll();


switch ($user['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'candidate':
        header("Location: candidate/dashboard.php");
        break;
    case 'student':
        header("Location: student/dashboard.php");
        break;
    default:
        $_SESSION['error'] = "Invalid user role";
        header("Location: login.php");
        break;
}
exit();
?>
