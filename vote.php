<?php
require_once 'includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($user['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} elseif ($user['role'] === 'candidate') {
    header("Location: candidate/dashboard.php");
    exit();
} elseif ($user['role'] === 'student') {
    header("Location: student/dashboard.php");
    exit();
} else {

    session_destroy();
    header("Location: login.php?error=invalid_role");
    exit();
}
?>