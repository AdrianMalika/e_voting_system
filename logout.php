<?php
session_start();
require_once 'config/database.php';

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only process logout if POST request (form submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // Log the logout action
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (user_id, action, details, ip_address) 
        VALUES (?, 'LOGOUT', ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        "User logged out successfully",
        $_SERVER['REMOTE_ADDR']
    ]);

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Set success message in new session
    session_start();
    $_SESSION['success'] = "You have been successfully logged out.";
    header("Location: login.php");
    exit();
}

// Get user data for display
$stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout - E-Voting System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-sign-out-alt fa-3x text-warning mb-3"></i>
                        <h3>Confirm Logout</h3>
                        <p class="text-muted mb-4">
                            Are you sure you want to logout, <?php echo htmlspecialchars($user['name']); ?>?
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <form method="POST">
                                <input type="hidden" name="confirm_logout" value="1">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Yes, Logout
                                </button>
                            </form>
                            <a href="<?php 
                                if ($user['role'] === 'admin') {
                                    echo 'admin/dashboard.php';
                                } elseif ($user['role'] === 'candidate') {
                                    echo 'candidate/dashboard.php';
                                } else {
                                    echo 'student/dashboard.php';
                                }
                            ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
