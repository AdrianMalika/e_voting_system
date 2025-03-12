<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $student_number = trim($_POST['student_number']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'student';
    $errors = [];

    // Validate inputs: All fields are required now.
    if (empty($name) || empty($email) || empty($student_number) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email is already taken. Please choose another email.";
    }
    
    // Check if student number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_number = ?");
    $stmt->execute([$student_number]);
    if ($stmt->fetch()) {
        $errors[] = "Student number already registered.";
    }

    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new student into the database
        $stmt = $conn->prepare("INSERT INTO users (name, email, student_number, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $student_number, $password_hash, $role]);

        $_SESSION['success'] = "Student added successfully.";
        header("Location: add_student.php");
        exit();
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <title>Add Student - E-Voting System</title>
  <style>
    .card {
      margin: 0 auto;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .card-body {
      padding: 20px;
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    .form-label {
      font-weight: bold;
      color: #555;
    }
    .form-control {
        width: 100%; 
      padding: 5px 10px; 
      font-size: 16px; 
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
      height: 35px; 
    }
    .btn-primary {
      background-color: #007bff;
      border: none;
      padding: 10px 15px;
      font-size: 16px;
      width: 100%;
      border-radius: 5px;
      color: #fff;
      cursor: pointer;
      margin-top: 10px;
    }
    .btn-primary:hover {
      background-color: #0056b3;
    }

</style>

</head>
<body>
  <!-- Display error messages -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Display success messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-xlg-9">
        <div class="card">
          <div class="card-body">
            <h2 class="text-center mb-4">Add Student</h2>
            <form class="form-horizontal form-material mx-2" method="POST" action="">
              <label for="name" class="form-label">Name</label>
              <div class="form-floating mb-3">
                <input type="text" name="name" id="name" class="form-control" required>
              </div>

              <label for="email" class="form-label">Email address</label>
              <div class="form-floating mb-3">
                <input type="email" name="email" id="email" pattern="[^ @]*@[^ @]*" class="form-control" required>
              </div>

              <label for="student_number" class="form-label">Student Number</label>
              <div class="form-floating mb-3">
                <input type="text" name="student_number" id="student_number" class="form-control" required>
              </div>

              <label for="password" class="form-label">Password</label>
              <div class="form-floating mb-3">
                <input type="password" name="password" id="password" class="form-control" required>
              </div>

              <label for="role" class="form-label">Role</label>
              <div class="form-floating mb-3">
                <select name="role" id="role" class="form-control">
                  <option value="student" selected>Student</option>
                </select>
              </div>

              <div class="form-group">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary text-white">Add Student</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php require_once '../includes/footer.php'; ?>
</body>
</html>
