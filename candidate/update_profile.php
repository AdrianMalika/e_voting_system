<?php

require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to update your profile";
    header("Location: ../login.php");
    exit();
}

// Check if user is a candidate
if ($user['role'] !== 'candidate') {
    $_SESSION['error'] = "Unauthorized access. Please login as a candidate.";
    header("Location: ../login.php");
    exit();
}

// Get candidate data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$candidate = $stmt->fetch();

if (!$candidate) {
    $_SESSION['error'] = "Candidate profile not found";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction
    $conn->beginTransaction();

    try {
        // Handle candidate-specific updates
        $position = trim($_POST['position'] ?? '');
        $manifesto = trim($_POST['manifesto'] ?? '');

        if (empty($position)) {
            throw new Exception("Position is required");
        }

        if (empty($manifesto)) {
            throw new Exception("Manifesto is required");
        }

        $sql = "UPDATE candidates SET position = ?, manifesto = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$position, $manifesto, $_SESSION['user_id']]);

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['photo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPEG, PNG and GIF are allowed");
            }

            $max_size = 5 * 1024 * 1024; // 5MB
            if ($_FILES['photo']['size'] > $max_size) {
                throw new Exception("File is too large. Maximum size is 5MB");
            }

            $upload_dir = '../uploads/candidates/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
                chmod($upload_dir, 0777); // Ensure directory is writable
            }

            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $file_name = 'candidate_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                chmod($target_path, 0644); // Make file readable
                $sql = "UPDATE candidates SET photo_url = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['uploads/candidates/' . $file_name, $_SESSION['user_id']]);
            } else {
                throw new Exception("Failed to upload photo");
            }
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Profile updated successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    // Redirect back to dashboard
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - E-Voting System</title>
</head>
<body>
    <div class="container py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Update Your Profile</h5>
                    </div>
                    <div class="card-body">
                        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <?php if ($candidate['photo_url']): ?>
                                        <img src="<?php echo '../' . htmlspecialchars($candidate['photo_url']); ?>" 
                                             class="img-fluid rounded mb-3" alt="Your photo">
                                    <?php else: ?>
                                        <div class="text-center p-4 bg-light rounded mb-3">
                                            <i class="fas fa-user-circle fa-5x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Update Photo</label>
                                        <input type="file" class="form-control" name="photo" accept="image/*">
                                        <div class="form-text">
                                            Max size: 5MB. Allowed types: JPEG, PNG, GIF
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Position</label>
                                        <input type="text" class="form-control" name="position" 
                                               value="<?php echo htmlspecialchars($candidate['position']); ?>" required>
                                        <div class="form-text">
                                            Enter the position you are running for
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Manifesto</label>
                                        <textarea class="form-control" name="manifesto" rows="6" required><?php 
                                            echo htmlspecialchars($candidate['manifesto']); 
                                        ?></textarea>
                                        <div class="form-text">
                                            Write your campaign manifesto and goals
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="dashboard.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>
