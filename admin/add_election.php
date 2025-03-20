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
        $required_fields = ['title', 'description', 'voting_start', 'voting_end'];
        
        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        if (empty($errors)) {
            $conn->beginTransaction();

            // Insert election
            $stmt = $conn->prepare("
                INSERT INTO elections (
                    title, description, 
                    start_date, end_date,
                    created_by, status
                ) VALUES (?, ?, ?, ?, ?, 'upcoming')
            ");

            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
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
                "New election created: {$_POST['title']}",
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
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="mb-0">Create New Election</h1>
                        <a href="manage_elections.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Elections
                        </a>
                    </div>
                    <p class="lead mt-2 mb-0">Configure all aspects of the new election</p>
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
.card {
    border: none;
    border-radius: 10px;
}
.btn {
    border-radius: 7px;
    padding: 0.75rem 1.5rem;
}
.form-control {
    border-radius: 7px;
    padding: 0.75rem 1rem;
}
.position-entry {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.bg-primary {
    background: linear-gradient(45deg, #4e73df, #224abe) !important;
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

<?php require_once '../includes/footer.php'; ?>
