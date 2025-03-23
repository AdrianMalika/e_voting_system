<?php
require_once '../includes/header.php';
require_once '../config/database.php';


if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $student_number = trim($_POST['student_number']);
    $branch = trim($_POST['branch']);
    $year_of_study = trim($_POST['year_of_study']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'student';
    $errors = [];

    // Validate inputs
    if (empty($name) || empty($email) || empty($student_number) || empty($password) || empty($branch) || empty($year_of_study)) {
        $errors[] = "All fields are required.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email is already taken. Please choose another email.";
    }
    
    // Check if student number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_number = ?");
    $stmt->execute([$student_number]);
    if ($stmt->fetch()) {
        $errors[] = "Student number already registered.";
    }

    if (empty($errors)) {
        // Insert new student into the database
        $stmt = $conn->prepare("INSERT INTO users (name, email, student_number, branch, year_of_study, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$name, $email, $student_number, $branch, $year_of_study, $password_hash, $role]);
            
            // Send welcome email with the generated credentials
            require '../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'imranttawakali@gmail.com';
                $mail->Password = 'lmrb lonc nqyh apfn';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Disable debug output in production
                $mail->SMTPDebug = 0;

                // Recipients
                $mail->setFrom('noreply.evoting@gmail.com', 'E-Voting System');
                $mail->addAddress($email, $name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your E-Voting System Login Credentials [Do Not Reply]';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2>Welcome to E-Voting System</h2>
                        <p>Dear {$name},</p>
                        <p>Your account has been successfully created. You can now login to the system using the following credentials:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            <p><strong>Email:</strong> {$email}</p>
                            <p><strong>Password:</strong> {$password}</p>
                            <p><strong>Branch:</strong> {$branch}</p>
                            <p><strong>Year of Study:</strong> {$year_of_study}</p>
                        </div>
                        <p>Please keep these credentials safe and do not share them with anyone.</p>
                        <p>You can login to the system by visiting our website and using these credentials.</p>
                        <p>Best regards,<br>E-Voting System Team</p>
                        <p style='color: #666; font-size: 12px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;'>
                            This is an automated message. Please do not reply to this email as the mailbox is not monitored.
                        </p>
                    </div>
                ";

                $mail->send();
                $_SESSION['success'] = "Student added successfully and welcome email sent.";
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Student added successfully and welcome email sent.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'add_student.php';
                    });
                </script>";
            } catch (Exception $e) {
                $_SESSION['success'] = "Student added successfully but failed to send email. Error: " . $mail->ErrorInfo;
                echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Partial Success',
                        text: 'Student added successfully but failed to send email.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'add_student.php';
                    });
                </script>";
            }

            header("Location: add_student.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Add this function after the main POST handling code
function generateSimplePassword() {
    // Define character sets
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Excluding I and O to avoid confusion
    $lowercase = 'abcdefghijkmnpqrstuvwxyz'; // Excluding l and o to avoid confusion
    $numbers = '23456789'; // Excluding 0 and 1 to avoid confusion
    
    $password = '';
    
    // Add one uppercase letter
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    
    // Add one number
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    
    // Add 4 lowercase letters
    for ($i = 0; $i < 4; $i++) {
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    }
    
    // Shuffle the password
    $password = str_shuffle($password);
    
    return $password;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Add Student - E-Voting System</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .step {
            transform-origin: center;
            transition: all 0.5s ease-in-out;
        }
        
        .slide-in {
            animation: slideIn 0.5s ease-out;
        }
        
        .slide-out {
            animation: slideOut 0.5s ease-in;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-100%);
            }
        }

        .floating-label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background-color: white;
            padding: 0 0.25rem;
            color: #6b7280;
            pointer-events: none;
            transition: all 0.2s ease-out;
        }

        .cool-input:focus + .floating-label,
        .cool-input:not(:placeholder-shown) + .floating-label {
            top: 0;
            transform: translateY(-50%) scale(0.85);
            color: #3b82f6;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .cool-input {
            width: 100%;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            background: transparent;
            @apply shadow-sm;
        }

        .cool-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .cool-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .cool-button::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .cool-button:hover::after {
            left: 100%;
        }

        @media (max-width: 640px) {
            .cool-input {
                padding: 0.875rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (min-width: 768px) {
            .input-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-indicator {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .step-indicator.active {
            background: #3b82f6;
            color: white;
            transform: scale(1.2);
        }

        .step-indicator.completed {
            background: #10b981;
            color: white;
        }

        .step-connector {
            position: absolute;
            height: 2px;
            background: #e5e7eb;
            width: 100%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
        }

        .connector-progress {
            position: absolute;
            height: 100%;
            background: #3b82f6;
            transition: width 0.3s ease;
        }

        .cool-input[type="select"] {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
        }

        .cool-input option {
            padding: 0.5rem;
            font-size: 1rem;
        }

        select.cool-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }
    </style>
</head>
<body>
    <!-- Display error messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Display success messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xlg-9">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-3xl font-bold text-center mb-8 text-gray-800 tracking-tight">
                            Add Student
                            <div class="h-1 w-24 bg-blue-600 mx-auto mt-2 rounded-full"></div>
                        </h2>
                        
                        <!-- Progress bar -->
                        <div class="progress-steps mb-8">
                            <div class="step-connector">
                                <div class="connector-progress" id="connector-progress"></div>
                            </div>
                            <div class="step-indicator active" id="step-1">1</div>
                            <div class="step-indicator" id="step-2">2</div>
                            <div class="step-indicator" id="step-3">3</div>
                            <div class="step-indicator" id="step-4">✓</div>
                        </div>
                        
                        <form class="form-horizontal mx-2 max-w-4xl mx-auto" method="POST" action="" id="multi-step-form">
                            <!-- Step 1: Personal Information -->
                            <div class="step" id="step1">
                                <div class="input-container">
                                    <div class="input-group">
                                        <input type="text" 
                                               name="name" 
                                               id="name" 
                                               class="cool-input peer" 
                                               placeholder=" "
                                               required>
                                        <label for="name" 
                                               class="floating-label">
                                            Full Name
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <input type="email" 
                                               name="email" 
                                               id="email" 
                                               class="cool-input peer" 
                                               placeholder=" "
                                               pattern="[^ @]*@[^ @]*" 
                                               required>
                                        <label for="email" 
                                               class="floating-label">
                                            Email Address
                                        </label>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button type="button" 
                                            onclick="nextStep(1)" 
                                            class="cool-button px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-300">
                                        Next
                                        <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Step 2: Student Details -->
                            <div class="step hidden" id="step2">
                                <div class="input-container">
                                    <div class="input-group">
                                        <input type="text" 
                                               name="student_number" 
                                               id="student_number" 
                                               class="cool-input peer" 
                                               placeholder=" "
                                               required>
                                        <label for="student_number" 
                                               class="floating-label">
                                            Student Number
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <select name="branch" 
                                                id="branch" 
                                                class="cool-input peer"
                                                required>
                                            <option value="">Select Branch</option>
                                            <option value="Blantyre">Blantyre</option>
                                            <option value="Lilongwe">Lilongwe</option>
                                            <option value="Zomba">Zomba</option>
                                        </select>
                                        <label for="branch" 
                                               class="floating-label">
                                            Branch
                                        </label>
                                    </div>
                                </div>
                                <div class="input-container">
                                    <div class="input-group">
                                        <select name="year_of_study" 
                                                id="year_of_study" 
                                                class="cool-input peer"
                                                required>
                                            <option value="">Select Year of Study</option>
                                            <option value="First Year">First Year</option>
                                            <option value="Second Year">Second Year</option>
                                            <option value="Third Year">Third Year</option>
                                            <option value="Fourth Year">Fourth Year</option>
                                        </select>
                                        <label for="year_of_study" 
                                               class="floating-label">
                                            Year of Study
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <select name="role" 
                                                id="role" 
                                                class="cool-input peer">
                                            <option value="student" selected>Student</option>
                                        </select>
                                        <label for="role" 
                                               class="floating-label">
                                            Role
                                        </label>
                                    </div>
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button type="button" 
                                            onclick="prevStep(2)" 
                                            class="cool-button px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Previous
                                    </button>
                                    <button type="button" 
                                            onclick="nextStep(2)" 
                                            class="cool-button px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Next
                                    </button>
                                </div>
                            </div>

                            <!-- Step 3: Password -->
                            <div class="step hidden" id="step3">
                                <div class="input-group relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="cool-input peer" 
                                           placeholder=" "
                                           required>
                                    <label for="password" 
                                           class="floating-label">
                                        Password
                                    </label>
                                    <div class="absolute right-24 top-1/2 transform -translate-y-1/2">
                                        <button type="button"
                                                id="toggle-password"
                                                class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <svg id="password-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="button"
                                            onclick="generateAndFillPassword()"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                        Generate
                                    </button>
                                </div>
                                <div class="text-sm text-gray-600 mt-2">
                                    Enter your desired password or click 'Generate' for a random secure password.
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button type="button" 
                                            onclick="prevStep(3)" 
                                            class="cool-button px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Previous
                                    </button>
                                    <button type="button" 
                                            onclick="nextStep(3)" 
                                            class="cool-button px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Next
                                    </button>
                                </div>
                            </div>

                            <!-- Step 4: Confirmation -->
                            <div class="step hidden" id="step4">
                                <div class="bg-white p-6 rounded-lg shadow-lg transform transition-all duration-300 hover:shadow-xl">
                                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Confirm Your Details</h3>
                                    <div class="space-y-4">
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Name:</span>
                                            <span id="confirm-name" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Email:</span>
                                            <span id="confirm-email" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Student Number:</span>
                                            <span id="confirm-student-number" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Branch:</span>
                                            <span id="confirm-branch" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Year of Study:</span>
                                            <span id="confirm-year" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-gray-600 w-1/3">Role:</span>
                                            <span id="confirm-role" class="font-medium text-gray-800"></span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-6">
                                        <button type="button" 
                                                onclick="prevStep(4)" 
                                                class="cool-button px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            Back
                                        </button>
                                        <button type="button" 
                                                onclick="showConfirmation()" 
                                                class="cool-button px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                            Confirm & Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Real-time Summary Card -->
                        <div class="mt-8 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-lg transform hover:scale-102 transition-all duration-300">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Real-time Form Summary
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="progress-item">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700">Form Completion</span>
                                        <span class="text-sm font-medium text-blue-600" id="completion-percentage">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" id="completion-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center">
                                    <span class="text-sm text-gray-600" id="fields-remaining">All fields remaining</span>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-6 gap-4">
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Name</div>
                                    <div class="flex items-center">
                                        <span id="name-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="name-text" class="text-sm truncate">Not filled</span>
                                    </div>
                                </div>
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Email</div>
                                    <div class="flex items-center">
                                        <span id="email-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="email-text" class="text-sm truncate">Not filled</span>
                                    </div>
                                </div>
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Student NO.</div>
                                    <div class="flex items-center">
                                        <span id="student-number-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="student-number-text" class="text-sm truncate">Not filled</span>
                                    </div>
                                </div>
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Branch</div>
                                    <div class="flex items-center">
                                        <span id="branch-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="branch-text" class="text-sm truncate">Not selected</span>
                                    </div>
                                </div>
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Year of Study</div>
                                    <div class="flex items-center">
                                        <span id="year-of-study-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="year-of-study-text" class="text-sm truncate">Not selected</span>
                                    </div>
                                </div>
                                <div class="field-status p-2 rounded-lg bg-white shadow-sm">
                                    <div class="text-xs text-gray-500">Password</div>
                                    <div class="flex items-center">
                                        <span id="password-status" class="w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                                        <span id="password-text" class="text-sm truncate">Not filled</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            let currentStep = 1;
                            const totalSteps = 4;

                            function updateProgressBar() {
                                // Calculate progress based on current step
                                let progress = (currentStep / totalSteps) * 100;
                                
                                // If we're on step 3 (password step) and password is filled, set to 100%
                                if (currentStep === 3 && document.getElementById('password').value.trim() !== '') {
                                    progress = 100;
                                }
                                
                                // Update the completion percentage and bar
                                const completionPercentage = document.getElementById('completion-percentage');
                                const completionBar = document.getElementById('completion-bar');
                                
                                if (completionPercentage) completionPercentage.textContent = `${progress}%`;
                                if (completionBar) {
                                    completionBar.style.width = `${progress}%`;
                                    completionBar.style.transition = 'width 0.5s ease-in-out';
                                }
                                
                                // Update fields remaining text
                                const fieldsRemaining = document.getElementById('fields-remaining');
                                if (fieldsRemaining) {
                                    if (progress === 100) {
                                        fieldsRemaining.textContent = 'All steps completed!';
                                    } else {
                                        const stepsLeft = totalSteps - currentStep;
                                        fieldsRemaining.textContent = `${stepsLeft} step${stepsLeft === 1 ? '' : 's'} remaining`;
                                    }
                                }
                            }

                            function updateStepIndicators(step) {
                                document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                                    if (index + 1 < step) {
                                        indicator.classList.add('completed');
                                        indicator.classList.remove('active');
                                    } else if (index + 1 === step) {
                                        indicator.classList.add('active');
                                        indicator.classList.remove('completed');
                                    } else {
                                        indicator.classList.remove('active', 'completed');
                                    }
                                });

                                // Update connector progress
                                const progress = ((step - 1) / (totalSteps - 1)) * 100;
                                document.getElementById('connector-progress').style.width = `${progress}%`;
                            }

                            function showStep(step) {
                                const currentStepElement = document.getElementById(`step${currentStep}`);
                                const nextStepElement = document.getElementById(`step${step}`);
                                
                                currentStepElement.classList.add('slide-out');
                                setTimeout(() => {
                                    currentStepElement.classList.add('hidden');
                                    currentStepElement.classList.remove('slide-out');
                                    
                                    nextStepElement.classList.remove('hidden');
                                    nextStepElement.classList.add('slide-in');
                                    
                                    setTimeout(() => {
                                        nextStepElement.classList.remove('slide-in');
                                    }, 500);
                                    
                                    currentStep = step;
                                    updateStepIndicators(step);
                                }, 500);
                            }

                            function nextStep(currentStep) {
                                if (validateStep(currentStep)) {
                                    if (currentStep === 3) {
                                        document.getElementById('confirm-name').textContent = document.getElementById('name').value;
                                        document.getElementById('confirm-email').textContent = document.getElementById('email').value;
                                        document.getElementById('confirm-student-number').textContent = document.getElementById('student_number').value;
                                        document.getElementById('confirm-branch').textContent = document.getElementById('branch').value;
                                        document.getElementById('confirm-year').textContent = document.getElementById('year_of_study').value;
                                        document.getElementById('confirm-role').textContent = document.getElementById('role').value;
                                    }
                                    showStep(currentStep + 1);
                                    updateProgressBar();
                                }
                            }

                            function prevStep(currentStep) {
                                showStep(currentStep - 1);
                                updateProgressBar();
                            }

                            function validateStep(step) {
                                const currentStepElement = document.getElementById(`step${step}`);
                                const inputs = currentStepElement.querySelectorAll('input, select');
                                let isValid = true;

                                inputs.forEach(input => {
                                    if (input.hasAttribute('required') && !input.value) {
                                        isValid = false;
                                        input.classList.add('border-red-500');
                                    } else {
                                        input.classList.remove('border-red-500');
                                    }
                                });

                                return isValid;
                            }

                            function showConfirmation() {
                                Swal.fire({
                                    title: 'Confirm Submission',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>Name:</strong> ${document.getElementById('name').value}</p>
                                            <p><strong>Email:</strong> ${document.getElementById('email').value}</p>
                                            <p><strong>Student Number:</strong> ${document.getElementById('student_number').value}</p>
                                            <p><strong>Branch:</strong> ${document.getElementById('branch').value}</p>
                                            <p><strong>Year of Study:</strong> ${document.getElementById('year_of_study').value}</p>
                                            <p><strong>Role:</strong> ${document.getElementById('role').value}</p>
                                        </div>
                                    `,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Submit',
                                    cancelButtonText: 'Cancel',
                                    confirmButtonColor: '#10B981',
                                    cancelButtonColor: '#6B7280',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Show loading state
                                        Swal.fire({
                                            title: 'Creating Student Account...',
                                            html: 'Please wait while we process your registration',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });
                                        
                                        // Submit the form
                                        document.getElementById('multi-step-form').submit();
                                    }
                                });
                            }

                            // Add input animation handlers
                            document.querySelectorAll('.cool-input').forEach(input => {
                                input.addEventListener('focus', () => {
                                    input.previousElementSibling?.classList.add('input-focused');
                                });
                                
                                input.addEventListener('blur', () => {
                                    if (!input.value) {
                                        input.previousElementSibling?.classList.remove('input-focused');
                                    }
                                });
                            });

                            // Remove progress bar update from form summary function
                            function updateFormSummary() {
                                const fields = {
                                    'name': document.getElementById('name'),
                                    'email': document.getElementById('email'),
                                    'student_number': document.getElementById('student_number'),
                                    'branch': document.getElementById('branch'),
                                    'year_of_study': document.getElementById('year_of_study'),
                                    'password': document.getElementById('password')
                                };

                                for (const [fieldName, element] of Object.entries(fields)) {
                                    if (!element) continue;

                                    const status = document.getElementById(`${fieldName.replace(/_/g, '-')}-status`);
                                    const text = document.getElementById(`${fieldName.replace(/_/g, '-')}-text`);
                                    
                                    if (!status || !text) continue;
                                    
                                    if (element.value && element.value.trim() !== '') {
                                        status.classList.remove('bg-gray-300');
                                        status.classList.add('bg-green-500');
                                        
                                        if (fieldName === 'password') {
                                            text.textContent = '••••••••';
                                        } else if (fieldName === 'year_of_study') {
                                            // Special handling for year of study select
                                            text.textContent = element.options[element.selectedIndex].text;
                                        } else {
                                            text.textContent = element.value.trim();
                                        }
                                    } else {
                                        status.classList.remove('bg-green-500');
                                        status.classList.add('bg-gray-300');
                                        
                                        // Custom "not selected" message for dropdowns
                                        if (element.tagName === 'SELECT') {
                                            text.textContent = 'Not selected';
                                        } else {
                                            text.textContent = 'Not filled';
                                        }
                                    }
                                }
                            }

                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function() {
                                // Set initial progress
                                const completionPercentage = document.getElementById('completion-percentage');
                                const completionBar = document.getElementById('completion-bar');
                                if (completionPercentage) completionPercentage.textContent = '0%';
                                if (completionBar) completionBar.style.width = '0%';

                                // Add input listeners for field status updates only
                                const formInputs = document.querySelectorAll('input, select');
                                formInputs.forEach(input => {
                                    ['input', 'change', 'keyup'].forEach(eventType => {
                                        input.addEventListener(eventType, updateFormSummary);
                                    });
                                });

                                updateFormSummary();

                                // Add password input listener
                                const passwordInput = document.getElementById('password');
                                if (passwordInput) {
                                    passwordInput.addEventListener('input', function() {
                                        if (currentStep === 3) {
                                            updateProgressBar();
                                        }
                                    });
                                }

                                const toggleButton = document.getElementById('toggle-password');
                                const passwordEye = document.getElementById('password-eye');

                                toggleButton.addEventListener('click', function() {
                                    if (passwordInput.type === 'password') {
                                        passwordInput.type = 'text';
                                        
                                        // Change to "hide" icon
                                        passwordEye.innerHTML = `
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        `;
                                    } else {
                                        passwordInput.type = 'password';
                                        
                                        // Change to "show" icon
                                        passwordEye.innerHTML = `
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        `;
                                    }
                                });
                            });

                            function generateAndFillPassword() {
                                fetch('generate_password.php')
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.password) {
                                            // Fill the password field
                                            document.getElementById('password').value = data.password;
                                            
                                            // Show success message
                                            Swal.fire({
                                                title: 'Password Generated!',
                                                text: `The password ${data.password} has been generated and filled in.`,
                                                icon: 'success',
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                            // Update form summary
                                            updateFormSummary();
                                            // Update progress bar if we're on step 3
                                            if (currentStep === 3) {
                                                updateProgressBar();
                                            }
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Failed to generate password. Please try again.',
                                            icon: 'error'
                                        });
                                    });
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
