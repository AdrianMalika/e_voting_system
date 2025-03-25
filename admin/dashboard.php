<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Please login as an administrator.";
    header("Location: ../login.php");
    exit();
}

// Get quick stats
$stats = [];

// Total Users
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$stats['students'] = $stmt->fetchColumn();

// Total Candidates
$stmt = $conn->query("SELECT COUNT(*) FROM candidates");
$stats['candidates'] = $stmt->fetchColumn();

// Pending Candidates
$stmt = $conn->query("SELECT COUNT(*) FROM candidates WHERE status = 'pending'");
$stats['pending_candidates'] = $stmt->fetchColumn();

// Active Elections
$stmt = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'");
$stats['active_elections'] = $stmt->fetchColumn();

// Total Votes
$stmt = $conn->query("SELECT COUNT(*) FROM votes");
$stats['total_votes'] = $stmt->fetchColumn();

// Recent Activities
$stmt = $conn->prepare("
    SELECT al.*, u.name, u.email 
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Add favicon -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 bg-gradient-to-r from-navy-700 to-navy-900 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h2>
                        <p class="text-navy-100 flex items-center">
                            <i class="fas fa-user-shield mr-2"></i>
                            Welcome back, Administrator
                        </p>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="bg-white/10 rounded-lg px-4 py-2 backdrop-blur-sm">
                        <p class="text-white text-sm flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 backdrop-blur-sm">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-user-graduate text-3xl text-navy-700 mb-3"></i>
                    <h6 class="text-gray-600 text-sm font-medium">Total Students</h6>
                    <h3 class="text-2xl font-bold text-navy-700"><?php echo $stats['students']; ?></h3>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-users text-3xl text-navy-700 mb-3"></i>
                    <h6 class="text-gray-600 text-sm font-medium">Total Candidates</h6>
                    <h3 class="text-2xl font-bold text-navy-700"><?php echo $stats['candidates']; ?></h3>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-user-clock text-3xl text-navy-700 mb-3"></i>
                    <h6 class="text-gray-600 text-sm font-medium">Pending Candidates</h6>
                    <h3 class="text-2xl font-bold text-navy-700"><?php echo $stats['pending_candidates']; ?></h3>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-vote-yea text-3xl text-navy-700 mb-3"></i>
                    <h6 class="text-gray-600 text-sm font-medium">Active Elections</h6>
                    <h3 class="text-2xl font-bold text-navy-700"><?php echo $stats['active_elections']; ?></h3>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-check-square text-3xl text-navy-700 mb-3"></i>
                    <h6 class="text-gray-600 text-sm font-medium">Total Votes</h6>
                    <h3 class="text-2xl font-bold text-navy-700"><?php echo $stats['total_votes']; ?></h3>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="manage_elections.php" class="block">
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 p-6">
                    <div class="text-center">
                        <div class="text-4xl text-navy-700 mb-4">
                            <i class="fas fa-poll"></i>
                        </div>
                        <h5 class="text-xl font-semibold text-gray-800 mb-2">Manage Elections</h5>
                        <p class="text-gray-600">Create and manage elections</p>
                    </div>
                </div>
            </a>
            <a href="candidates.php" class="block">
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 p-6">
                    <div class="text-center">
                        <div class="text-4xl text-navy-700 mb-4">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="text-xl font-semibold text-gray-800 mb-2">Manage Candidates</h5>
                        <p class="text-gray-600">Approve candidates and add them to elections</p>
                    </div>
                </div>
            </a>
            <a href="admin_settings.php" class="block">
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 p-6 border-2 border-navy-100">
                    <div class="text-center">
                        <div class="text-4xl text-navy-700 mb-4">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h5 class="text-xl font-semibold text-gray-800 mb-2">Admin Settings</h5>
                        <p class="text-gray-600">Update your credentials and profile</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-history mr-2 text-navy-700"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_activities as $activity): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($activity['user_id']): ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['email']); ?></div>
                                    <?php else: ?>
                                        <span class="text-gray-500">System</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-navy-100 text-navy-800">
                                        <?php echo htmlspecialchars($activity['action']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($activity['details']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <button class="inline-flex items-center px-4 py-2 bg-navy-700 hover:bg-navy-800 text-white text-sm font-medium rounded-md transition-colors duration-300">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View All Activities
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Tailwind config for custom colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            100: '#E7E9EF',
                            200: '#C7CCE0',
                            300: '#A6AED1',
                            400: '#8691C2',
                            500: '#6573B3',
                            600: '#4456A4',
                            700: '#1E3A8A',
                            800: '#1B3479',
                            900: '#182E68'
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
