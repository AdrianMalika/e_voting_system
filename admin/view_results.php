<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!$user || $user['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$election_id) {
    header("Location: elections.php");
    exit();
}

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

if (!$election) {
    $_SESSION['error'] = "Election not found";
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Vote Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Vote Comparison</h5>
                </div>
                <div class="card-body">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Election Status</h5>
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
                            <p class="mb-1"><strong>Total Votes Cast:</strong></p>
                            <p class="text-muted"><?php echo $election['total_votes']; ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Voter Turnout:</strong></p>
                            <p class="text-muted"><?php echo $turnout_percentage; ?>% (<?php echo $stats['unique_voters']; ?> of <?php echo $stats['total_eligible_voters']; ?>)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Candidate Results</h5>
        </div>
        <div class="card-body">
            <?php if (empty($candidates)): ?>
                <p class="text-muted mb-0">No candidates found for this election.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Candidate</th>
                                <th>Position</th>
                                <th>Student Number</th>
                                <th>Votes</th>
                                <th>Percentage</th>
                                <th>Graph</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            $prev_votes = null;
                            $display_rank = 1;
                            
                            foreach ($candidates as $index => $candidate): 
                                // If current votes are different from previous, update display rank
                                if ($prev_votes !== $candidate['vote_count']) {
                                    $display_rank = $rank;
                                }
                                $prev_votes = $candidate['vote_count'];
                            ?>
                                <tr>
                                    <td><?php echo $display_rank; ?></td>
                                    <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
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
                            <?php 
                                $rank++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($has_ended): ?>
        <!-- Download Results Button -->
        <div class="mt-4 text-end">
            <button type="button" class="btn btn-success" onclick="exportResults()">
                <i class="fas fa-download me-2"></i>Download Results
            </button>
        </div>

        <script>
        function exportResults() {
            // Create CSV content
            let csv = 'Rank,Candidate,Position,Student Number,Votes,Percentage\n';
            
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [
                    cells[0].textContent, // Rank
                    cells[1].textContent, // Candidate
                    cells[2].textContent, // Position
                    cells[3].textContent, // Student Number
                    cells[4].textContent, // Votes
                    cells[5].textContent  // Percentage
                ];
                csv += rowData.join(',') + '\n';
            });

            // Create and trigger download
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', 'election_results_<?php echo $election_id; ?>.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
        </script>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Prepare data for charts
const candidates = <?php echo json_encode(array_map(function($candidate) {
    return [
        'name' => $candidate['name'],
        'votes' => $candidate['vote_count'],
        'percentage' => $candidate['vote_percentage']
    ];
}, $candidates)); ?>;

// Setup colors array
const colors = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF9F40'
];

// Pie Chart
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: candidates.map(c => c.name),
        datasets: [{
            data: candidates.map(c => c.votes),
            backgroundColor: colors.slice(0, candidates.length),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(2);
                        return `${context.label}: ${value} votes (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Bar Chart
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: candidates.map(c => c.name),
        datasets: [{
            label: 'Number of Votes',
            data: candidates.map(c => c.votes),
            backgroundColor: colors.slice(0, candidates.length),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.raw} votes (${candidates[context.dataIndex].percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>
