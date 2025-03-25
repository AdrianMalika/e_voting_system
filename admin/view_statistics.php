<?php
require_once '../includes/header.php';

// Sanitize function for IDs
function sanitize_id($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9_]+/', '_', $string);
    return $string;
}

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'election_id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    $_SESSION['error'] = "Invalid election ID";
    header("Location: manage_elections.php");
    exit();
}

// Get voting statistics
$stmt = $conn->prepare("
    SELECT 
        n.nomination_id,
        n.first_name,
        n.surname,
        n.role,  -- Use 'role' instead of 'position'
        COUNT(v.candidate_id) AS vote_count
    FROM nominations n
    LEFT JOIN votes v ON n.nomination_id = v.candidate_id AND n.election_id = v.election_id
    WHERE n.election_id = ?
    GROUP BY n.role, n.nomination_id  -- Group by 'role' instead of 'position'
    ORDER BY n.role, vote_count DESC
");
$stmt->execute([$election_id]);
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the roles
$role_votes = [];
foreach ($statistics as $stat) {
    $role_votes[$stat['role']][] = $stat;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 1rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .chart-container {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }
        .role-buttons {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h2 class="mb-4">Voting Statistics for Election</h2>

        <!-- Buttons for each role -->
        <div class="role-buttons">
            <?php foreach ($role_votes as $role => $role_stats): ?>
                <button class="btn btn-primary me-2" onclick="showRoleChart('<?php echo sanitize_id($role); ?>')">
                    <?php echo htmlspecialchars($role); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($statistics)): ?>
            <?php foreach ($role_votes as $role => $role_stats): ?>
                <!-- Hidden chart for each role -->
                <div id="chart-<?php echo sanitize_id($role); ?>" class="chart-container" style="display: none;">
                    <canvas id="voteChart-<?php echo sanitize_id($role); ?>"></canvas>
                </div>

                <script>
                    // Create a bar chart for each role
                    const voteChartData_<?php echo sanitize_id($role); ?> = {
                        labels: <?php echo json_encode(array_map(function($stat) {
                            return $stat['first_name'] . ' ' . $stat['surname'];
                        }, $role_stats)); ?>,
                        datasets: [{
                            label: '<?php echo htmlspecialchars($role); ?> Votes',
                            data: <?php echo json_encode(array_map(function($stat) {
                                return $stat['vote_count'];
                            }, $role_stats)); ?>,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    };

                    const ctx_<?php echo sanitize_id($role); ?> = document.getElementById('voteChart-<?php echo sanitize_id($role); ?>').getContext('2d');
                    const voteChart_<?php echo sanitize_id($role); ?> = new Chart(ctx_<?php echo sanitize_id($role); ?>, {
                        type: 'bar',
                        data: voteChartData_<?php echo sanitize_id($role); ?>,
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            responsive: true
                        }
                    });
                </script>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No votes have been cast yet for this election.</div>
        <?php endif; ?>

        <div class="mb-4">
            <a href="view_election.php?election_id=<?php echo urlencode($election_id); ?>" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Election
            </a>
        </div>
    </div>

    <script>
        // Function to show/hide charts based on the role clicked
        function showRoleChart(roleId) {
            // Hide all charts
            const allCharts = document.querySelectorAll('.chart-container');
            allCharts.forEach(chart => chart.style.display = 'none');

            // Show the clicked chart
            const selectedChart = document.getElementById('chart-' + roleId);
            selectedChart.style.display = 'block';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
