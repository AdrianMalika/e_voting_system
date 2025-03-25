<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';
require_once '../includes/election_status.php';

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $user['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$election = null;
$positions = [];

// Get election ID
$election_id = $_GET['election_id'] ?? 0;

// Verify election exists and user can access it
$stmt = $conn->prepare("
    SELECT e.*, u.branch as user_branch 
    FROM elections e, users u 
    WHERE e.id = ? AND u.id = ? AND e.branch = u.branch AND e.status = 'active'
");
$stmt->execute([$election_id, $_SESSION['user_id']]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    $_SESSION['error'] = "Invalid election or unauthorized access.";
    header("Location: elections.php");
    exit();
}

// Check if user has already voted for each position
$stmt = $conn->prepare("
    SELECT DISTINCT position_name 
    FROM votes 
    WHERE election_id = ? AND voter_id = ?
");
$stmt->execute([$election_id, $_SESSION['user_id']]);
$votedPositions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get candidates grouped by position (INCLUDING PROGRAM & YEAR OF STUDY)
$stmt = $conn->prepare("
    SELECT 
        n.nomination_id,
        n.first_name,
        n.surname,
        n.photo_path,
        n.manifesto,
        n.role as position_name,
        n.branch,
        n.program,
        n.year_of_study
    FROM nominations n
    WHERE n.election_id = ? 
    AND n.status = 'approved'
    AND n.branch = ?
    ORDER BY n.role, n.first_name
");
$stmt->execute([$election_id, $election['branch']]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
foreach ($candidates as $candidate) {
    $positions[$candidate['position_name']][] = $candidate;
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Validate one vote per position
        if (isset($_POST['votes'])) {
            foreach ($_POST['votes'] as $position => $candidate_id) {
                if (in_array($position, $votedPositions)) {
                    throw new Exception("You have already voted for $position.");
                }

                // Insert vote
                $stmt = $conn->prepare("
                    INSERT INTO votes (election_id, position_name, candidate_id, voter_id, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$election_id, $position, $candidate_id, $_SESSION['user_id']]);
                $votedPositions[] = $position; // Add this position to the list of voted positions
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Your votes have been recorded successfully!";
        header("Location: view_elections.php?branch=" . $election['branch']);
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --custom-primary-color: #3498db; 
            --custom-secondary-color: #2c3e50;
        }

        /* Apply the colors */
        .bg-custom-primary {
            background-color: var(--custom-primary-color);
        }

        .bg-custom-secondary {
            background-color: var(--custom-secondary-color);
        }
        .candidate-img {
            width: 100px; 
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4B6F44; /* Green border for better visibility */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .candidate-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
        }
        .candidate-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .candidate-info {
            flex-grow: 1;
        }
        .candidate-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        .candidate-details {
            font-size: 0.9rem;
            color: #666;
        }
        .vote-button {
            background-color: #4B6F44;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .vote-button:hover {
            background-color: #396F31;
        }
        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            width: 50%;
            max-width: 500px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-6">
        
        <!-- Election Header -->
        <div class="bg-custom-primary text-white mb-4 p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-1"><?php echo htmlspecialchars($election['title']); ?></h2>
                    <p class="text-lg"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($election['branch']); ?> Branch</p>
                </div>
                <a href="view_elections.php?branch=<?php echo urlencode($election['branch']); ?>" 
                   class="bg-custom-secondary text-white px-4 py-2 rounded-lg shadow hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-600 text-white p-4 rounded-lg mb-4 text-center">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="voteForm">
            <?php foreach ($positions as $position => $position_candidates): ?>
                <div class="border border-gray-300 mb-4 rounded-lg shadow-lg">
                    <div class="bg-gray-100 p-4 rounded-t-lg">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($position); ?></h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($position_candidates as $candidate): ?>
                                <div class="candidate-card p-4 flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo htmlspecialchars($candidate['photo_path']); ?>" alt="Candidate Photo" class="candidate-img mr-4">
                                        <div class="candidate-info">
                                            <h5 class="candidate-name"><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?></h5>
                                            <p class="candidate-details">Program: <?php echo htmlspecialchars($candidate['program']); ?></p>
                                            <p class="candidate-details">Year of Study: <?php echo htmlspecialchars($candidate['year_of_study']); ?></p>
                                            <button type="button" class="text-blue-600 underline" onclick="viewManifesto('<?php echo htmlspecialchars(addslashes($candidate['manifesto'])); ?>')">View Manifesto</button>
                                        </div>
                                    </div>
                                    <?php if (in_array($position, $votedPositions)): ?>
                                        <div class="text-gray-500">You have voted for this position.</div>
                                    <?php else: ?>
                                        <div>
                                            <input type="radio" name="votes[<?php echo htmlspecialchars($position); ?>]" value="<?php echo $candidate['nomination_id']; ?>" class="mr-2">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="mt-4">
                <button type="submit" class="vote-button">
                    Submit Votes
                </button>
            </div>
        </form>
    </div>

    <!-- Modal for Viewing Manifesto -->
    <div id="manifestoModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex justify-center items-center">
        <div class="modal-content">
            <h3 class="text-xl font-semibold mb-4">Candidate Manifesto</h3>
            <p id="manifestoText"></p>
            <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg" onclick="closeManifesto()">Close</button>
        </div>
    </div>

    <script>
    function viewManifesto(manifesto) {
        document.getElementById('manifestoText').innerText = manifesto;
        document.getElementById('manifestoModal').classList.remove('hidden');
    }

    function closeManifesto() {
        document.getElementById('manifestoModal').classList.add('hidden');
    }
    </script>
</body>
</html>
