<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to update your profile";
    header("Location: ../login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email)) {
        $_SESSION['error'] = "Name and email are required fields";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email is already taken";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Check if student number is already taken by another user
    if (!empty($student_number)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE student_number = ? AND id != ?");
        $stmt->execute([$student_number, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Student number is already taken";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        // Update basic info
        $sql = "UPDATE users SET name = ?, email = ?, student_number = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $email, $student_number ?: null, $user_id]);

        // Handle password update if requested
        if (!empty($current_password) && !empty($new_password)) {
            // Verify current password
            if (!password_verify($current_password, $user['password_hash'])) {
                throw new Exception("Current password is incorrect");
            }

            // Validate new password
            if (strlen($new_password) < 8) {
                throw new Exception("New password must be at least 8 characters long");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match");
            }

            // Update password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$password_hash, $user_id]);
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Profile updated successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

// Redirect back to previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
