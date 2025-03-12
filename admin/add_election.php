<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';


if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $start_date = filter_input(INPUT_POST, 'start_date');
    $end_date = filter_input(INPUT_POST, 'end_date');
    $positions = isset($_POST['positions']) ? array_map(function($pos) {
        return trim(htmlspecialchars($pos, ENT_QUOTES, 'UTF-8'));
    }, $_POST['positions']) : [];
    $positions = array_filter($positions);

    // Validate required fields
    if (!$title || !$description || !$start_date || !$end_date || empty($positions)) {
        $error = "All fields are required";
    } else {
        // Convert dates to MySQL format
        $start_datetime = date('Y-m-d H:i:s', strtotime($start_date));
        $end_datetime = date('Y-m-d H:i:s', strtotime($end_date));

        // Validate dates
        if (strtotime($start_date) >= strtotime($end_date)) {
            $error = "End date must be after start date";
        } elseif (strtotime($start_date) < time()) {
            $error = "Start date cannot be in the past";
        } else {
            try {
                $conn->beginTransaction();

                
                $stmt = $conn->prepare("
                    INSERT INTO elections (title, description, start_date, end_date, created_by, status)
                    VALUES (?, ?, ?, ?, ?, 'upcoming')
                ");
                
                if (!$stmt->execute([$title, $description, $start_datetime, $end_datetime, $user['id']])) {
                    throw new Exception("Failed to create election");
                }
                
                $election_id = $conn->lastInsertId();

                // Insert positions for this election
                $stmt = $conn->prepare("
                    INSERT INTO election_positions (election_id, position_name)
                    VALUES (?, ?)
                ");

                foreach ($positions as $position) {
                    if (!empty(trim($position))) {
                        if (!$stmt->execute([$election_id, trim($position)])) {
                            throw new Exception("Failed to add position: " . htmlspecialchars($position));
                        }
                    }
                }

                // Log the action
                $stmt = $conn->prepare("
                    INSERT INTO audit_logs (user_id, action, details, ip_address)
                    VALUES (?, 'CREATE_ELECTION', ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    "Created election: $title",
                    $_SERVER['REMOTE_ADDR']
                ]);

                // Create notification for admin
                add_notification(
                    $conn,
                    'new_election',
                    "New election created: $title (Starting: " . date('M j, Y', strtotime($start_datetime)) . ")",
                    $election_id,
                    'election',
                    $user['id']
                );

                // Get all students and create notifications for them
                $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'student'");
                $stmt->execute();
                $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($students as $student_id) {
                    // Check if this student already has a notification for this election
                    $stmt = $conn->prepare("
                        SELECT COUNT(*) FROM notifications 
                        WHERE user_id = ? AND reference_id = ? AND type = 'new_election'
                    ");
                    $stmt->execute([$student_id, $election_id]);
                    $exists = $stmt->fetchColumn();
                    
                    if (!$exists) {
                        add_notification(
                            $conn,
                            'new_election',
                            "A new election '$title' has been created and will start on " . date('M j, Y', strtotime($start_datetime)),
                            $election_id,
                            'election',
                            $student_id
                        );
                    }
                }

                $conn->commit();
                $success = "Election created successfully";

                // Clear form data
                $title = $description = $start_date = $end_date = '';
                $positions = [];
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Error creating election: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Election</h2>
        <a href="elections.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Elections
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="title" class="form-label">Election Title</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    <div class="invalid-feedback">Please provide an election title.</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                        echo isset($description) ? htmlspecialchars($description) : ''; 
                    ?></textarea>
                    <div class="invalid-feedback">Please provide a description.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                               value="<?php echo isset($start_date) ? $start_date : ''; ?>" required>
                        <div class="invalid-feedback">Please select a start date and time.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                               value="<?php echo isset($end_date) ? $end_date : ''; ?>" required>
                        <div class="invalid-feedback">Please select an end date and time.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Positions</label>
                    <div id="positions-container">
                        <?php 
                        $positions = isset($positions) ? $positions : [''];
                        foreach ($positions as $index => $position): 
                        ?>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="positions[]" 
                                       value="<?php echo htmlspecialchars($position); ?>" 
                                       placeholder="Enter position name" required>
                                <?php if ($index === 0): ?>
                                    <button type="button" class="btn btn-primary" onclick="addPosition()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger" onclick="removePosition(this)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Create Election
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function addPosition() {
    const container = document.getElementById('positions-container');
    const newPosition = document.createElement('div');
    newPosition.className = 'input-group mb-2';
    newPosition.innerHTML = `
        <input type="text" class="form-control" name="positions[]" 
               placeholder="Enter position name" required>
        <button type="button" class="btn btn-danger" onclick="removePosition(this)">
            <i class="fas fa-minus"></i>
        </button>
    `;
    container.appendChild(newPosition);
}

function removePosition(button) {
    button.closest('.input-group').remove();
}

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php require_once '../includes/footer.php'; ?>
