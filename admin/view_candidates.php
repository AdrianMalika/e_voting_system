<?php
require_once '../includes/header.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}

// Validate election ID
if (!isset($_GET['election_id']) || !is_numeric($_GET['election_id'])) {
    $_SESSION['error'] = "Invalid election ID";
    header("Location: manage_elections.php");
    exit();
}

$election_id = $_GET['election_id'];

// Get election details
$stmt = $conn->prepare("
    SELECT title, description, start_date, end_date
    FROM elections 
    WHERE id = ?
");
$stmt->execute([$election_id]);
$election = $stmt->fetch();

if (!$election) {
    $_SESSION['error'] = "Election not found";
    header("Location: manage_elections.php");
    exit();
}

// Get approved candidates
$stmt = $conn->prepare("
    SELECT 
        ec.id as candidate_entry_id,
        c.id as candidate_id,
        u.name as candidate_name,
        u.email,
        c.position,
        c.photo_url,
        c.manifesto,
        ec.created_at as approval_date
    FROM election_candidates ec
    JOIN candidates c ON ec.candidate_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE ec.election_id = ? AND ec.status = 'approved'
    ORDER BY c.position, u.name
");
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Candidates - <?php echo htmlspecialchars($election['title']); ?></title>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-gradient-primary text-white p-4 rounded-3 shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="display-6 mb-1"><?php echo htmlspecialchars($election['title']); ?></h2>
                            <p class="lead mb-0">
                                Election Period: <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                            </p>
                        </div>
                        <a href="manage_elections.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Elections
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Candidates Grid -->
        <div class="row g-4">
            <?php if (empty($candidates)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No approved candidates found for this election.
                    </div>
                </div>
            <?php else: ?>
                <?php 
                $current_position = '';
                foreach ($candidates as $candidate): 
                    if ($current_position != $candidate['position']):
                        $current_position = $candidate['position'];
                ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h3 class="text-primary mb-0"><?php echo htmlspecialchars($candidate['position']); ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-hover">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <?php if ($candidate['photo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($candidate['photo_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($candidate['candidate_name']); ?>"
                                             class="rounded-circle candidate-photo mb-2">
                                    <?php else: ?>
                                        <div class="default-avatar mb-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($candidate['candidate_name']); ?></h5>
                                    <p class="text-muted small"><?php echo htmlspecialchars($candidate['email']); ?></p>
                                </div>
                                
                                <?php if ($candidate['manifesto']): ?>
                                    <div class="manifesto-section">
                                        <h6 class="fw-bold">Manifesto:</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .bg-gradient-primary {
        background: linear-gradient(45deg, #2c3e50, #3498db);
    }

    .text-primary {
        color: #2c3e50 !important;
    }

    .candidate-photo {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 3px solid #3498db;
    }

    .default-avatar {
        width: 120px;
        height: 120px;
        background-color: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #2c3e50;
        margin: 0 auto;
        border: 3px solid #3498db;
    }

    .shadow-hover {
        transition: all 0.3s ease;
    }

    .shadow-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .manifesto-section {
        border-top: 1px solid #dee2e6;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .card {
        border-radius: 0.5rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-title {
        color: #2c3e50;
        font-weight: 600;
    }

    .btn-light {
        background-color: rgba(255, 255, 255, 0.9);
        border: none;
        font-weight: 500;
    }

    .btn-light:hover {
        background-color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }

    h3.text-primary {
        font-size: 1.5rem;
        font-weight: 600;
    }
    </style>
</body>
</html>

<?php require_once '../includes/footer.php'; ?> 