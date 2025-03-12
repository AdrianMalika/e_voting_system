<?php
require_once '../includes/header.php';
require_once '../includes/election_status.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    header("Location: elections.php");
    exit();
}

// Update election statuses
update_election_status($conn);

// Get election details
$stmt = $conn->prepare("
    SELECT e.*, 
           COUNT(DISTINCT v.id) as total_votes,
           (SELECT COUNT(*) FROM election_candidates ec WHERE ec.election_id = e.id) as total_candidates
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    WHERE e.id = ?
    GROUP BY e.id
");
$stmt->execute([$election_id]);
$election = $stmt->fetch();

if (!$election || $election['status'] === 'upcoming') {
    $_SESSION['error'] = "Election results are not available yet";
    header("Location: elections.php");
    exit();
}

// Get candidates and their vote counts
$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.position,
        u.name,
        u.student_number,
        COUNT(v.id) as vote_count,
        COALESCE(
            ROUND((COUNT(v.id) / NULLIF((
                SELECT COUNT(*) 
                FROM votes 
                WHERE election_id = ?
            ), 0)) * 100, 2),
            0
        ) as vote_percentage
    FROM election_candidates ec
    JOIN candidates c ON ec.candidate_id = c.id
    JOIN users u ON c.user_id = u.id
    LEFT JOIN votes v ON v.candidate_id = c.id AND v.election_id = ?
    WHERE ec.election_id = ?
    GROUP BY c.id, u.name, u.student_number, c.position
    ORDER BY vote_count DESC, u.name ASC
");
$stmt->execute([$election_id, $election_id, $election_id]);
$candidates = $stmt->fetchAll();

// Get voting statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT voter_id) as unique_voters,
        (
            SELECT COUNT(*) 
            FROM users 
            WHERE role = 'student'
        ) as total_eligible_voters
    FROM votes
    WHERE election_id = ?
");
$stmt->execute([$election_id]);
$stats = $stmt->fetch();

$turnout_percentage = $stats['total_eligible_voters'] > 0 
    ? round(($stats['unique_voters'] / $stats['total_eligible_voters']) * 100, 2)
    : 0;

// Check if election has ended
$has_ended = strtotime($election['end_date']) < time();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Election Results: <?php echo htmlspecialchars($election['title']); ?></h2>
        <a href="elections.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Elections
        </a>
    </div>

    <?php if (!$has_ended): ?>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>
            This election is still ongoing. Results shown here are live and may change.
        </div>
    <?php endif; ?>

    <!-- Election Status Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Election Information</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Start Date:</strong></p>
                            <p class="text-muted"><?php echo date('M j, Y g:i a', strtotime($election['start_date'])); ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>End Date:</strong></p>
                            <p class="text-muted"><?php echo date('M j, Y g:i a', strtotime($election['end_date'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Voting Statistics</h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Total Votes:</strong></p>
                            <p class="text-muted"><?php echo $election['total_votes']; ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Voter Turnout:</strong></p>
                            <p class="text-muted"><?php echo $turnout_percentage; ?>%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Vote Distribution</h5>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="votePieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Votes per Candidate</h5>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="voteBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabular Results -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Results</h5>
        </div>
        <div class="card-body">
            <?php if (empty($candidates)): ?>
                <p class="text-muted">No candidates found for this election.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Candidate</th>
                                <th>Student Number</th>
                                <th>Votes</th>
                                <th>Percentage</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['student_number']); ?></td>
                                    <td><?php echo $candidate['vote_count']; ?></td>
                                    <td><?php echo $candidate['vote_percentage']; ?>%</td>
                                    <td style="width: 200px;">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $candidate['vote_percentage']; ?>%"
                                                 aria-valuenow="<?php echo $candidate['vote_percentage']; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for charts
    const candidates = <?php echo json_encode(array_map(function($c) {
        return [
            'name' => $c['name'],
            'votes' => $c['vote_count'],
            'percentage' => $c['vote_percentage'],
            'position' => $c['position']
        ];
    }, $candidates)); ?>;

    // Generate colors for charts
    const colors = candidates.map((_, i) => 
        `hsl(${(i * 360) / candidates.length}, 70%, 60%)`
    );

    // Setup Pie Chart
    new Chart(document.getElementById('votePieChart'), {
        type: 'pie',
        data: {
            labels: candidates.map(c => `${c.name} (${c.position})`),
            datasets: [{
                data: candidates.map(c => c.votes),
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Setup Bar Chart
    new Chart(document.getElementById('voteBarChart'), {
        type: 'bar',
        data: {
            labels: candidates.map(c => c.name),
            datasets: [{
                label: 'Votes',
                data: candidates.map(c => c.votes),
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
