<?php
session_start();
require_once 'config/database.php';

// Check if admin exists
$adminExists = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        $name = trim($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        $student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : null;
        $registration_key = isset($_POST['registration_key']) ? trim($_POST['registration_key']) : '';
        
        // Validation
        $errors = [];
        
        if (empty($name) || empty($email) || empty($password)) {
            $errors[] = "All fields are required";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        // Role-specific validation
        if ($role === 'admin') {
            // Admin registration key (you should change this to a secure value)
            $valid_admin_key = "admin123secure";
            if ($registration_key !== $valid_admin_key) {
                $errors[] = "Invalid admin registration key";
            }
        } elseif ($role === 'student' || $role === 'candidate') {
            if (empty($student_number)) {
                $errors[] = "Student number is required";
            }
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered";
        }
        
        // Check if student number already exists (for students and candidates)
        if ($student_number) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE student_number = ?");
            $stmt->execute([$student_number]);
            if ($stmt->fetch()) {
                $errors[] = "Student number already registered";
            }
        }
        
        if (empty($errors)) {
            // Insert user
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password_hash, role, student_number) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role,
                $student_number
            ]);
            
            $user_id = $conn->lastInsertId();
            
            // If registering as candidate, create candidate profile
            if ($role === 'candidate') {
                $stmt = $conn->prepare("
                    INSERT INTO candidates (user_id, position, status) 
                    VALUES (?, 'Unspecified', 'pending')
                ");
                $stmt->execute([$user_id]);
            }
            
            // Log the registration
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address) 
                VALUES (?, 'REGISTRATION', ?, ?)
            ");
            $stmt->execute([
                $user_id,
                "New $role registration",
                $_SERVER['REMOTE_ADDR']
            ]);
            
            $conn->commit();
            $success_message = "Registration successful! You can now login.";
            
            // Redirect to login page after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $conn->rollBack();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $errors[] = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="text-center mb-4">
                <h2>E-Voting System</h2>
                <p class="text-muted">Create your account</p>
            </div>
            
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">Register</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" id="registerForm">
                        <!-- Role Selection -->
                        <div class="role-selector">
                            <?php if (!$adminExists): ?>
                            <div class="role-option" onclick="selectRole('admin')">
                                <i class="fas fa-user-shield"></i>
                                <div>Admin</div>
                            </div>
                            <?php endif; ?>
                            <div class="role-option" onclick="selectRole('student')">
                                <i class="fas fa-user-graduate"></i>
                                <div>Student</div>
                            </div>
                            <div class="role-option" onclick="selectRole('candidate')">
                                <i class="fas fa-user-tie"></i>
                                <div>Candidate</div>
                            </div>
                        </div>

                        <input type="hidden" name="role" id="selectedRole" value="student">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-3 student-candidate-field">
                            <label class="form-label">
                                <i class="fas fa-id-card me-2"></i>Student Number
                            </label>
                            <input type="text" class="form-control" name="student_number">
                        </div>
                        
                        <div class="mb-3 admin-field" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-key me-2"></i>Admin Registration Key
                            </label>
                            <input type="password" class="form-control" name="registration_key">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-decoration-none">Login here</a>
                            </p>
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
            document.getElementById('selectedRole').value = role;
            
            // Show/hide fields based on role
            if (role === 'admin') {
                document.querySelector('.admin-field').style.display = 'block';
                document.querySelector('.student-candidate-field').style.display = 'none';
            } else {
                document.querySelector('.admin-field').style.display = 'none';
                document.querySelector('.student-candidate-field').style.display = 'block';
            }
        }
        
        // Set initial role
        selectRole('student');
    </script>
</body>
</html>
