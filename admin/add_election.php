<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['title', 'description', 'branch', 'voting_start', 'voting_end'];
        
        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        // Validate branch
        $valid_branches = ['Blantyre', 'Lilongwe', 'Mzuzu'];
        if (!in_array($_POST['branch'], $valid_branches)) {
            $errors[] = "Invalid branch selected";
        }

        if (empty($errors)) {
            $conn->beginTransaction();

            // Insert election
            $stmt = $conn->prepare("
                INSERT INTO elections (
                    title, description, branch,
                    start_date, end_date,
                    created_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, 'upcoming')
            ");

            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['branch'],
                $_POST['voting_start'],
                $_POST['voting_end'],
                $_SESSION['user_id']
            ]);

            $election_id = $conn->lastInsertId();

            // Insert positions
            foreach ($_POST['positions'] as $index => $position) {
                $stmt = $conn->prepare("
                    INSERT INTO election_positions (
                        election_id, position_name, position_description,
                        required_year, max_candidates
                    ) VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $election_id,
                    $position,
                    $_POST['position_descriptions'][$index],
                    $_POST['position_years'][$index],
                    $_POST['position_max_candidates'][$index]
                ]);
            }

            // Create notifications
            add_notification(
                $conn,
                'new_election',
                "New election created: {$_POST['title']} ({$_POST['branch']} Branch)",
                $election_id,
                'election',
                $_SESSION['user_id']
            );

            $conn->commit();
            $_SESSION['success'] = "Election created successfully";
            header("Location: manage_elections.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $errors[] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white p-4 rounded-3 shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="display-6 mb-1">Create New Election</h2>
                        <p class="lead mb-0">Configure all aspects of the new election</p>
                    </div>
                    <a href="manage_elections.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Elections
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <!-- Basic Information -->
                <div class="mb-4">
                    <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title">Election Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="branch">Branch</label>
                            <select class="form-select" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="Blantyre">Blantyre</option>
                                <option value="Lilongwe">Lilongwe</option>
                                <option value="Zomba">Zomba</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="mb-4">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Voting Period</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="voting_start" required>
                        </div>
                        <div class="col-md-6">
                            <label>End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="voting_end" required>
                        </div>
                    </div>
                </div>

                <!-- Positions -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Election Positions</h5>
                        <button type="button" class="btn btn-secondary" onclick="addPosition()">
                            <i class="fas fa-plus me-2"></i>Add Position
                        </button>
                    </div>
                    <div id="positions-container">
                        <!-- Position template will be added here -->
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Create Election
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(45deg, #2c3e50, #3498db);
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
}

.btn {
    border-radius: 7px;
    padding: 0.75rem 1.5rem;
}

.btn-primary {
    background-color: #2c3e50;
    border-color: #2c3e50;
}

.btn-primary:hover {
    background-color: #34495e;
    border-color: #34495e;
}

.form-control, .form-select {
    border-radius: 7px;
    padding: 0.75rem 1rem;
}

.position-entry {
    background-color: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.position-entry:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
}

.shadow-hover {
    transition: all 0.3s ease;
}

.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

h5 {
    color: #2c3e50;
}

.btn-secondary {
    background-color: #3498db;
    border-color: #3498db;
}

.btn-secondary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}
</style>

<script>
function addPosition() {
    const container = document.getElementById('positions-container');
    const positionHtml = `
        <div class="position-entry">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Position Details</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePosition(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Position Name</label>
                    <input type="text" class="form-control" name="positions[]" required>
                </div>
                <div class="col-md-6">
                    <label>Position Description</label>
                    <input type="text" class="form-control" name="position_descriptions[]" required>
                </div>
                <div class="col-md-6">
                    <label>Required Year</label>
                    <select class="form-select" name="position_years[]">
                        <option value="">Any Year</option>
                        <option value="1">First Year</option>
                        <option value="2">Second Year</option>
                        <option value="3">Third Year</option>
                        <option value="4">Fourth Year</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Max Candidates</label>
                    <input type="number" class="form-control" name="position_max_candidates[]" min="1" value="1" required>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', positionHtml);
}

function removePosition(button) {
    button.closest('.position-entry').remove();
}

document.addEventListener('DOMContentLoaded', function() {
    addPosition();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../includes/footer.php'; ?>
 