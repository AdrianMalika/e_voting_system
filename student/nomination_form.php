<?php
require_once '../includes/header.php';

// Ensure user is logged in and is a student or candidate
if (!isset($_SESSION['user_id']) || !in_array($user['role'], ['student', 'candidate'])) {
    header("Location: ../login.php");
    exit();
}

// Configuration for application period (this could be fetched from a database or config file)
$applicationStart = strtotime("2024-01-01 00:00:00");
$applicationEnd = strtotime("2024-01-15 23:59:59");
$currentTimestamp = time();

// Check if the current time is within the application period
$isApplicationOpen = $currentTimestamp >= $applicationStart && $currentTimestamp <= $applicationEnd;

// Function to validate file upload
function validateFileUpload($file, $allowedTypes, $maxSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                return "File exceeds maximum size limit";
            case UPLOAD_ERR_FORM_SIZE:
                return "File exceeds form maximum size limit";
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            default:
                return "Unknown upload error";
        }
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return "File size exceeds limit of " . ($maxSize / 1048576) . "MB";
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return "Invalid file type. Allowed types: " . implode(', ', $allowedTypes);
    }

    return null;
}

// Add this function at the top of your file after the validateFileUpload function
function checkExistingUser($conn, $userId, $studentId, $email) {
    $query = "SELECT user_id, student_id, email FROM nominations WHERE user_id = :user_id OR student_id = :student_id OR email = :email";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':student_id' => $studentId,
        ':email' => $email
    ]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $errors = [];
    foreach ($result as $row) {
        if ($row['user_id'] == $userId) {
            $errors[] = "You have already submitted a nomination";
        }
        if ($row['student_id'] === $studentId) {
            $errors[] = "Student ID already registered as a candidate";
        }
        if ($row['email'] === $email) {
            $errors[] = "Email address already registered as a candidate";
        }
    }
    
    return $errors;
}

// Add this after your existing validateFileUpload function
function createUploadDirectories() {
    $baseUploadDir = '../uploads/';
    $directories = [
        'photos' => $baseUploadDir . 'profile_photos/',
        'transcripts' => $baseUploadDir . 'academic_transcripts/',
    ];

    // Create directories if they don't exist
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    return $directories;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Initialize error array
        $errors = [];

        // Validate required fields
        $required_fields = ['first_name', 'surname', 'email', 'phone', 'student_id', 
                          'program', 'year_of_study', 'gpa', 'role', 'branch'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Validate phone number
        if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
            $errors[] = "Invalid phone number format";
        }

        // Validate GPA
        if (!is_numeric($_POST['gpa']) || $_POST['gpa'] < 0 || $_POST['gpa'] > 4.0) {
            $errors[] = "Invalid GPA value";
        }

        // Validate file uploads
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedDocTypes = ['application/pdf', 'application/msword', 
                          'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        // Validate profile photo
        if (isset($_FILES['profile_photo'])) {
            $photoError = validateFileUpload($_FILES['profile_photo'], $allowedImageTypes, $maxFileSize);
            if ($photoError) {
                $errors[] = "Profile photo error: " . $photoError;
            }
        }

        // Validate transcript
        if (isset($_FILES['transcript'])) {
            $transcriptError = validateFileUpload($_FILES['transcript'], $allowedDocTypes, $maxFileSize);
            if ($transcriptError) {
                $errors[] = "Transcript error: " . $transcriptError;
            }
        }

        // Check for existing user ID, student ID and email
        if (empty($errors)) {
            $existingUserErrors = checkExistingUser(
                $conn,
                $_SESSION['user_id'],
                htmlspecialchars(trim($_POST['student_id'])), 
                filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)
            );
            
            if (!empty($existingUserErrors)) {
                $_SESSION['error_message'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <h4 class='alert-heading'><i class='fas fa-exclamation-triangle me-2'></i>Registration Error</h4>
                    <ul class='mb-0'>
                        " . implode('</li><li>', array_map(function($error) {
                            return "<li>$error</li>";
                        }, $existingUserErrors)) . "
                    </ul>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                
                // Keep the form data except for the duplicated fields
                $_SESSION['form_data'] = $_POST;
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }

        // If there are no errors, proceed with submission
        if (empty($errors)) {
            $conn->beginTransaction();

            try {
                // Create upload directories
                $uploadDirs = createUploadDirectories();
                
                // Handle profile photo upload
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $photoFileName = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                    $photoPath = $uploadDirs['photos'] . $photoFileName;

                    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $photoPath)) {
                        throw new Exception("Failed to upload profile photo");
                    }
                }

                // Handle transcript upload
                if (isset($_FILES['transcript']) && $_FILES['transcript']['error'] === UPLOAD_ERR_OK) {
                    $transcriptFileName = uniqid() . '_' . basename($_FILES['transcript']['name']);
                    $transcriptPath = $uploadDirs['transcripts'] . $transcriptFileName;

                    if (!move_uploaded_file($_FILES['transcript']['tmp_name'], $transcriptPath)) {
                        throw new Exception("Failed to upload academic transcript");
                    }
                }

                // Sanitize input data
                $firstName = htmlspecialchars(trim($_POST['first_name']));
                $surname = htmlspecialchars(trim($_POST['surname']));
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $phone = htmlspecialchars(trim($_POST['phone']));
                $studentId = htmlspecialchars(trim($_POST['student_id']));
                $program = htmlspecialchars(trim($_POST['program']));
                $yearOfStudy = intval($_POST['year_of_study']);
                $gpa = floatval($_POST['gpa']);
                $role = htmlspecialchars(trim($_POST['role']));
                $branch = htmlspecialchars(trim($_POST['branch']));

                // Insert nomination into database using PDO
                $query = "INSERT INTO nominations (
                    user_id, first_name, surname, email, phone, 
                    student_id, program, year_of_study, gpa, role, 
                    branch, photo_path, transcript_path, submission_date
                ) VALUES (
                    :user_id, :first_name, :surname, :email, :phone,
                    :student_id, :program, :year_of_study, :gpa, :role,
                    :branch, :photo_path, :transcript_path, NOW()
                )";

                $stmt = $conn->prepare($query);
                
                // Bind parameters using PDO named parameters
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':first_name' => $firstName,
                    ':surname' => $surname,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':student_id' => $studentId,
                    ':program' => $program,
                    ':year_of_study' => $yearOfStudy,
                    ':gpa' => $gpa,
                    ':role' => $role,
                    ':branch' => $branch,
                    ':photo_path' => $photoPath,
                    ':transcript_path' => $transcriptPath
                ]);

                // Get the nomination ID
                $nominationId = $conn->lastInsertId();

                // Insert into nomination_documents table
                $docQuery = "INSERT INTO nomination_documents (
                    nomination_id, document_type, file_path, file_name, file_size, mime_type
                ) VALUES (
                    :nomination_id, :document_type, :file_path, :file_name, :file_size, :mime_type
                )";
                
                $docStmt = $conn->prepare($docQuery);

                // Insert profile photo document
                $photoSize = filesize($photoPath);
                $photoMime = mime_content_type($photoPath);
                $docStmt->execute([
                    ':nomination_id' => $nominationId,
                    ':document_type' => 'photo',
                    ':file_path' => $photoPath,
                    ':file_name' => $photoFileName,
                    ':file_size' => $photoSize,
                    ':mime_type' => $photoMime
                ]);

                // Insert transcript document
                $transcriptSize = filesize($transcriptPath);
                $transcriptMime = mime_content_type($transcriptPath);
                $docStmt->execute([
                    ':nomination_id' => $nominationId,
                    ':document_type' => 'transcript',
                    ':file_path' => $transcriptPath,
                    ':file_name' => $transcriptFileName,
                    ':file_size' => $transcriptSize,
                    ':mime_type' => $transcriptMime
                ]);

                $conn->commit();
                $_SESSION['success_message'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <h4 class='alert-heading'><i class='fas fa-check-circle me-2'></i>Application Submitted Successfully!</h4>
                    <p>Thank you for submitting your candidate application. Your information has been received and will be reviewed by the Election Committee.</p>
                    <hr>
                    <p class='mb-0'>Position: <strong>{$role}</strong><br>Branch: <strong>{$branch}</strong></p>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                
                // Clear the form data
                $_POST = array();
                
                // Refresh the page to show the success message and clear the form
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();

            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                error_log("Nomination Error: " . $e->getMessage());
                
                // Clean up uploaded files if they exist
                if (isset($photoPath) && file_exists($photoPath)) {
                    unlink($photoPath);
                }
                if (isset($transcriptPath) && file_exists($transcriptPath)) {
                    unlink($transcriptPath);
                }
            }
        } else {
            $_SESSION['error_message'] = "Please correct the following errors:<br>" . 
                                       implode("<br>", $errors);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
        error_log("Unexpected Error: " . $e->getMessage());
    }
}

// Display error/success messages if they exist
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

// Display Messages
if (isset($_SESSION['success_message'])): ?>
    <div class="container mt-3">
        <?php 
        echo $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        ?>
    </div>

    <script>
    // Automatically scroll to the success message
    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Enable Bootstrap alerts to be dismissed
    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        new bootstrap.Alert(alert);
    });
    </script>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error</h4>
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="bg-custom-primary text-white p-4 rounded-3 shadow">
                <h1 class="display-4 fw-bold">Candidate Application</h1>
                <p class="lead mb-0">Apply to become a candidate in the upcoming student elections</p>
                <hr class="my-4 opacity-25">
                <div id="countdown-timer" class="mb-3"></div>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#guidelinesModal">
                    <i class="fas fa-info-circle me-2"></i>
                    View Application Guidelines
                </button>
            </div>
        </div>
    </div>

    <!-- Nomination Form -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4 text-center">Candidate Information</h3>
                    <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                        <!-- Disable form if application is closed -->
                        <?php if (!$isApplicationOpen): ?>
                            <div class="alert alert-danger">The application period is closed.</div>
                        <?php else: ?>
                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h5 class="text-custom-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                            value="<?php echo isset($formData['first_name']) ? htmlspecialchars($formData['first_name']) : ''; ?>" 
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="surname" class="form-label">Surname</label>
                                        <input type="text" class="form-control" id="surname" name="surname" 
                                            value="<?php echo isset($formData['surname']) ? htmlspecialchars($formData['surname']) : ''; ?>" 
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                            value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>" 
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                            value="<?php echo isset($formData['phone']) ? htmlspecialchars($formData['phone']) : ''; ?>" 
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="mb-4">
                                <h5 class="text-custom-primary mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Academic Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="student_id" class="form-label">Student ID</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" 
                                            value="<?php echo isset($formData['student_id']) ? htmlspecialchars($formData['student_id']) : ''; ?>" 
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="program" class="form-label">Program of Study</label>
                                        <input type="text" class="form-control" id="program" name="program" 
                                            value="<?php echo isset($formData['program']) ? htmlspecialchars($formData['program']) : ''; ?>" 
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year_of_study" class="form-label">Year of Study</label>
                                        <select class="form-select" id="year_of_study" name="year_of_study" required>
                                            <option value="">Select Year</option>
                                            <option value="1">First Year</option>
                                            <option value="2">Second Year</option>
                                            <option value="3">Third Year</option>
                                            <option value="4">Fourth Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gpa" class="form-label">Current GPA</label>
                                        <input type="number" step="0.01" class="form-control" id="gpa" name="gpa" 
                                            value="<?php echo isset($formData['gpa']) ? htmlspecialchars($formData['gpa']) : ''; ?>" 
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Position Information -->
                            <div class="mb-4">
                                <h5 class="text-custom-primary mb-3">
                                    <i class="fas fa-briefcase me-2"></i>Position Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Position Applying For</label>
                                        <select id="role" name="role" class="form-select" required>
                                            <option value="">Select Position</option>
                                            <option value="President">President</option>
                                            <option value="Vice President">Vice President</option>
                                            <option value="Secretary">Secretary</option>
                                            <option value="Treasurer">Treasurer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="branch" class="form-label">Campus Branch</label>
                                        <select id="branch" name="branch" class="form-select" required>
                                            <option value="">Select Branch</option>
                                            <option value="Blantyre">Blantyre</option>
                                            <option value="Lilongwe">Lilongwe</option>
                                            <option value="Zomba">Zomba</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Required Documents -->
                            <div class="mb-4">
                                <h5 class="text-custom-primary mb-3">
                                    <i class="fas fa-file-upload me-2"></i>Required Documents
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="profile_photo" class="form-label">Profile Photo</label>
                                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transcript" class="form-label">Academic Transcript</label>
                                        <input type="file" class="form-control" id="transcript" name="transcript" accept=".pdf,.doc,.docx" required>
                                    </div>
                               
                                </div>
                            </div>

                            <!-- Declaration -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="declaration" required>
                                    <label class="form-check-label" for="declaration">
                                        I declare that all information provided is true and accurate. I understand that any false information may result in disqualification.
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-custom-primary w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidelines Modal -->
<div class="modal fade" id="guidelinesModal" tabindex="-1" aria-labelledby="guidelinesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-custom-primary text-white">
                <h5 class="modal-title" id="guidelinesModalLabel">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Application Guidelines
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Eligibility Section -->
                    <div class="col-12">
                        <h6 class="fw-bold text-custom-primary">
                            <i class="fas fa-user-check me-2"></i>Eligibility Requirements
                        </h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Must be a registered student in good academic standing</li>
                            <li class="list-group-item">Minimum GPA of 2.5 or equivalent</li>
                            <li class="list-group-item">No disciplinary records</li>
                            <li class="list-group-item">Must be enrolled for the entire academic year</li>
                        </ul>
                    </div>

                    <!-- Important Dates -->
                    <div class="col-12">
                        <h6 class="fw-bold text-custom-primary">
                            <i class="fas fa-calendar-alt me-2"></i>Important Dates
                        </h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Application Opening: January 1, 2024</li>
                            <li class="list-group-item">Application Deadline: January 15, 2024</li>
                            <li class="list-group-item">Campaign Period: January 20-30, 2024</li>
                            <li class="list-group-item">Election Day: February 1, 2024</li>
                        </ul>
                    </div>

                    <!-- Required Documents -->
                    <div class="col-12">
                        <h6 class="fw-bold text-custom-primary">
                            <i class="fas fa-file-alt me-2"></i>Required Documents
                        </h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Valid Student ID</li>
                            <li class="list-group-item">Academic Transcript</li>
                            <li class="list-group-item">Letter of Intent</li>
                            <li class="list-group-item">Two Reference Letters</li>
                        </ul>
                    </div>

                    <!-- Additional Information -->
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> All applications will be reviewed by the Election Committee. 
                            Successful candidates will be notified via email within 48 hours of submission.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-custom-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>I Understand
                </button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --custom-primary-color:  #2c3e50;
    --custom-secondary-color: #3498db;
}

.bg-custom-primary {
    background-color: var(--custom-primary-color) !important;
}

.text-custom-primary {
    color: var(--custom-primary-color) !important;
}

.btn-custom-primary {
    background-color: var(--custom-primary-color);
    border-color: var(--custom-primary-color);
    color: #fff;
}

.btn-custom-primary:hover {
    background-color: rgb(25, 58, 92);
    border-color: rgb(25, 58, 92);
    color: #fff;
}

.card {
    border-radius: 15px;
}

.modal-content {
    border-radius: 15px;
}

.list-group-item {
    border-left: none;
    border-right: none;
    padding: 1rem;
}

.modal-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.alert {
    border-radius: 15px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert-heading {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.alert hr {
    border-top-color: inherit;
    opacity: 0.2;
}

.alert .btn-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fadeIn 0.5s ease-out;
}
</style>

<script>
// JavaScript to handle the countdown timer
document.addEventListener('DOMContentLoaded', function() {
    const applicationEnd = new Date(<?php echo $applicationEnd * 1000; ?>); // Convert to milliseconds
    const countdownElement = document.getElementById('countdown-timer');

    function updateCountdown() {
        const now = new Date();
        const timeRemaining = applicationEnd - now;

        if (timeRemaining <= 0) {
            countdownElement.textContent = "Application period has ended.";
            document.querySelector('form').querySelectorAll('input, select, button').forEach(function(element) {
                element.disabled = true;
            });
        } else {
            const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

            countdownElement.textContent = `Time remaining: ${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
});

// Form submission confirmation
document.querySelector('form').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to submit your candidate application?')) {
        e.preventDefault();
    }
});

// Clear form after successful submission
<?php if (isset($_SESSION['success_message'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').reset();
});
<?php endif; ?>

// Preview image before upload
document.getElementById('profile_photo').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            if (document.getElementById('preview-image')) {
                document.getElementById('preview-image').remove();
            }
            var preview = document.createElement('img');
            preview.id = 'preview-image';
            preview.src = e.target.result;
            preview.style.maxWidth = '200px';
            preview.style.marginTop = '10px';
            preview.className = 'rounded shadow-sm';
            document.getElementById('profile_photo').parentNode.appendChild(preview);
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Highlight fields with errors
    <?php if (isset($_SESSION['error_message']) && strpos($_SESSION['error_message'], 'Student ID already registered') !== false): ?>
    document.getElementById('student_id').classList.add('is-invalid');
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message']) && strpos($_SESSION['error_message'], 'Email address already registered') !== false): ?>
    document.getElementById('email').classList.add('is-invalid');
    <?php endif; ?>

    // Add input event listeners to remove invalid class when user starts typing
    ['student_id', 'email'].forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }
    });
});

// Add custom styling for invalid fields
const style = document.createElement('style');
style.textContent = `
    .is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
`;
document.head.appendChild(style);
</script>

<?php require_once '../includes/footer.php'; ?>

