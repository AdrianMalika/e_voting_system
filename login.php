<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT u.*, c.status as candidate_status 
        FROM users u 
        LEFT JOIN candidates c ON u.id = c.user_id 
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check if candidate is approved
        if ($user['role'] === 'candidate' && $user['candidate_status'] !== 'approved') {
            $error = "Your candidate account is pending approval. Please wait for admin approval.";
            
            // Log failed login attempt
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address) 
                VALUES (?, 'LOGIN_FAILED', ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                "Candidate account not yet approved",
                $_SERVER['REMOTE_ADDR']
            ]);
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Log successful login
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address) 
                VALUES (?, 'LOGIN', ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                "User logged in successfully",
                $_SERVER['REMOTE_ADDR']
            ]);

            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'candidate':
                    header('Location: candidate/dashboard.php');
                    break;
                case 'student':
                    header('Location: student/dashboard.php');
                    break;
            }
            exit;
        }
    } else {
        $error = "Invalid email or password";
        
        // Log failed login attempt
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (action, details, ip_address) 
            VALUES ('LOGIN_FAILED', ?, ?)
        ");
        $stmt->execute([
            "Failed login attempt for email: $email",
            $_SERVER['REMOTE_ADDR']
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Voting System</title>
    <link rel="icon" href="https://mchs.mw/img/mchs_logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
        }
        .role-selector {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        .role-option {
            text-align: center;
            cursor: pointer;
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .role-option:hover, .role-option.active {
            background: #e9ecef;
        }
        .role-option i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #3498db;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: #3498db;
            border: none;
            padding: 12px;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .home-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #3498db;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h2 style="display: inline;">E-Voting System</h2>
                <div class="home-icon">
                    <a href="index.php" class="text-decoration-none" onclick="return confirm('Are you sure you want to go back to the home page?');">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
                <p class="text-muted">Login to access your account</p>
            </div>
            
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="role-selector">
                        <div class="role-option" onclick="selectRole('student')">
                            <i class="fas fa-user-graduate"></i>
                            <div>Student</div>
                        </div>
                        <div class="role-option" onclick="selectRole('candidate')">
                            <i class="fas fa-user-tie"></i>
                            <div>Candidate</div>
                        </div>
                        <div class="role-option" onclick="selectRole('admin')">
                            <i class="fas fa-user-shield"></i>
                            <div>Admin</div>
                        </div>
                    </div>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRole(role) {
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
        }
    </script>
</body>
</html>
