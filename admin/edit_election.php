<?php
require_once '../includes/header.php';
require_once '../includes/notifications.php';

if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get election ID from URL
$election_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$election_id) {
    header("Location: elections.php");
    exit();
}

$error = null;
$success = null;

// Fetch existing election data
try {
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE id = ?
    ");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$election) {
        header("Location: elections.php");
        exit();
    }

    // Fetch existing positions
    $stmt = $conn->prepare("
        SELECT id, position_name 
        FROM election_positions 
        WHERE election_id = ?
    ");
    $stmt->execute([$election_id]);
    $existing_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error loading election: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $start_date = filter_input(INPUT_POST, 'start_date');
    $end_date = filter_input(INPUT_POST, 'end_date');
    $positions = isset($_POST['positions']) ? array_map(function($pos) {
        return trim(htmlspecialchars($pos, ENT_QUOTES, 'UTF-8'));
    }, $_POST['positions']) : [];
    $positions = array_filter($positions);
    $position_ids = $_POST['position_ids'] ?? [];

    if (!$title || !$description || !$start_date || !$end_date || empty($positions)) {
        $error = "All fields are required";
    } else {
        $start_datetime = date('Y-m-d H:i:s', strtotime($start_date));
        $end_datetime = date('Y-m-d H:i:s', strtotime($end_date));

        if (strtotime($start_date) >= strtotime($end_date)) {
            $error = "End date must be after start date";
        } else {
            try {
                $conn->beginTransaction();

                // Update election details
                $stmt = $conn->prepare("
                    UPDATE elections 
                    SET title = ?, description = ?, start_date = ?, end_date = ?
                    WHERE id = ?
                ");
                
                if (!$stmt->execute([$title, $description, $start_datetime, $end_datetime, $election_id])) {
                    throw new Exception("Failed to update election");
                }

                // Update or insert positions
                $stmt_update = $conn->prepare("
                    UPDATE election_positions 
                    SET position_name = ? 
                    WHERE id = ? AND election_id = ?
                ");
                
                $stmt_insert = $conn->prepare("
                    INSERT INTO election_positions (election_id, position_name)
                    VALUES (?, ?)
                ");

                foreach ($positions as $index => $position) {
                    if (!empty(trim($position))) {
                        if (isset($position_ids[$index]) && $position_ids[$index]) {
                            // Update existing position
                            if (!$stmt_update->execute([$position, $position_ids[$index], $election_id])) {
                                throw new Exception("Failed to update position: " . htmlspecialchars($position));
                            }
                        } else {
                            // Insert new position
                            if (!$stmt_insert->execute([$election_id, $position])) {
                                throw new Exception("Failed to add position: " . htmlspecialchars($position));
                            }
                        }
                    }
                }

                // Delete removed positions
                $kept_position_ids = array_filter($position_ids);
                if (!empty($kept_position_ids)) {
                    $stmt = $conn->prepare("
                        DELETE FROM election_positions 
                        WHERE election_id = ? AND id NOT IN (" . str_repeat('?,', count($kept_position_ids) - 1) . "?)
                    ");
                    $params = array_merge([$election_id], $kept_position_ids);
                    $stmt->execute($params);
                }

                // Log the action
                $stmt = $conn->prepare("
                    INSERT INTO audit_logs (user_id, action, details, ip_address)
                    VALUES (?, 'UPDATE_ELECTION', ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    "Updated election: $title",
                    $_SERVER['REMOTE_ADDR']
                ]);

                $conn->commit();
                $success = "Election updated successfully";
                
                // Refresh election data
                $stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
                $stmt->execute([$election_id]);
                $election = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("SELECT id, position_name FROM election_positions WHERE election_id = ?");
                $stmt->execute([$election_id]);
                $existing_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Error updating election: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Election</h2>
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
                           value="<?php echo htmlspecialchars($election['title']); ?>" required>
                    <div class="invalid-feedback">Please provide an election title.</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                        echo htmlspecialchars($election['description']); 
                    ?></textarea>
                    <div class="invalid-feedback">Please provide a description.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($election['start_date'])); ?>" required>
                        <div class="invalid-feedback">Please select a start date and time.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($election['end_date'])); ?>" required>
                        <div class="invalid-feedback">Please select an end date and time.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Positions</label>
                    <div id="positions-container">
                        <?php foreach ($existing_positions as $index => $position): ?>
                            <div class="input-group mb-2">
                                <input type="hidden" name="position_ids[]" value="<?php echo $position['id']; ?>">
                                <input type="text" class="form-control" name="positions[]" 
                                       value="<?php echo htmlspecialchars($position['position_name']); ?>" 
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
                        <i class="fas fa-save me-2"></i>Update Election
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
        <input type="hidden" name="position_ids[]" value="">
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