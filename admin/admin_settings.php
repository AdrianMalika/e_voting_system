<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Please login as an administrator.";
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $new_email = $_POST['email'];
    $new_name = $_POST['name'];
    $new_branch = $_POST['branch'];

    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_hash = $stmt->fetchColumn();

    if (password_verify($current_password, $current_hash)) {
        if ($new_password === $confirm_password) {
            // Update user information
            $stmt = $conn->prepare("
                UPDATE users 
                SET name = ?, 
                    email = ?,
                    password_hash = ?,
                    branch = ?
                WHERE id = ?
            ");
            
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->execute([$new_name, $new_email, $password_hash, $new_branch, $_SESSION['user_id']]);
            
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: admin_settings.php");
            exit();
        } else {
            $_SESSION['error'] = "New passwords do not match!";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect!";
    }
}

// Get current user info
$stmt = $conn->prepare("SELECT name, email, branch FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - E-Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-8 bg-white p-6 rounded-xl shadow-md">
                <div class="flex items-center space-x-4">
                    <div class="bg-navy-700 p-3 rounded-lg">
                        <i class="fas fa-cog text-2xl text-white animate-spin-slow"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Admin Settings</h2>
                        <p class="text-gray-600 mt-1">Manage your account credentials and profile information</p>
                    </div>
                </div>
                <a href="dashboard.php" class="flex items-center text-navy-700 hover:text-navy-800 transition-colors duration-300 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-3xl mx-auto">
                <!-- Profile Header -->
                <div class="bg-navy-700 px-6 py-4">
                    <div class="flex items-center">
                        <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center">
                            <i class="fas fa-user-shield text-3xl text-navy-700"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-white text-xl font-semibold"><?php echo htmlspecialchars($admin['name']); ?></h3>
                            <p class="text-navy-100 text-sm">Administrator Account</p>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <form method="POST" action="" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <h4 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-2">
                                <i class="fas fa-user mr-2 text-navy-700"></i>
                                Profile Information
                            </h4>
                            
                            <!-- Name Input -->
                            <div class="relative">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="name" id="name" 
                                           value="<?php echo htmlspecialchars($admin['name']); ?>"
                                           class="pl-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                           required>
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div class="relative">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email" 
                                           value="<?php echo htmlspecialchars($admin['email']); ?>"
                                           class="pl-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                           required>
                                </div>
                            </div>

                            <!-- Branch Input -->
                            <div class="relative">
                                <label for="branch" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <select name="branch" id="branch" 
                                            class="pl-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                            required>
                                        <option value="">Select Branch</option>
                                        <option value="Blantyre" <?php echo ($admin['branch'] === 'Blantyre') ? 'selected' : ''; ?>>Blantyre</option>
                                        <option value="Lilongwe" <?php echo ($admin['branch'] === 'Lilongwe') ? 'selected' : ''; ?>>Lilongwe</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <h4 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-2">
                                <i class="fas fa-lock mr-2 text-navy-700"></i>
                                Security Settings
                            </h4>

                            <!-- Current Password -->
                            <div class="relative">
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="pl-10 pr-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                           required>
                                    <button type="button" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer password-toggle"
                                            onclick="togglePassword('current_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="relative">
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="new_password" id="new_password" 
                                           class="pl-10 pr-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                           required>
                                    <button type="button" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer password-toggle"
                                            onclick="togglePassword('new_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="relative">
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="confirm_password" id="confirm_password" 
                                           class="pl-10 pr-10 w-full rounded-md border-gray-300 focus:border-navy-700 focus:ring focus:ring-navy-200 focus:ring-opacity-50 transition-shadow duration-200 text-sm"
                                           required>
                                    <button type="button" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer password-toggle"
                                            onclick="togglePassword('confirm_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button Section -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="window.location.href='dashboard.php'" 
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500">
                                <i class="fas fa-times mr-1.5"></i>
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-700 hover:bg-navy-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 transition-colors duration-200">
                                <i class="fas fa-save mr-1.5"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Security Tips Card -->
            <div class="mt-4 bg-white rounded-lg shadow-md p-4 max-w-3xl mx-auto">
                <h5 class="text-sm font-semibold text-gray-800 mb-2">
                    <i class="fas fa-shield-alt text-navy-700 mr-2"></i>
                    Security Tips
                </h5>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Use a strong password with at least 8 characters
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Include numbers, symbols, and uppercase letters
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Don't reuse passwords from other accounts
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            100: '#E7E9EF',
                            200: '#CBD5E1',
                            700: '#0A1E47',
                            800: '#061433',
                        }
                    },
                    animation: {
                        'spin-slow': 'spin 3s linear infinite',
                    }
                }
            }
        }

        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
