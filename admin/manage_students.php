<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../login.php");
    exit();
}

// Handle AJAX Requests (Edit & Delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "edit") {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $email = $_POST["email"];
        $student_number = $_POST["student_number"];
        $password = $_POST["password"];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, student_number = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$name, $email, $student_number, $hashed_password, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, student_number = ? WHERE id = ?");
            $stmt->execute([$name, $email, $student_number, $id]);
        }

        echo json_encode(["success" => true, "id" => $id, "name" => $name, "email" => $email, "student_number" => $student_number]);
        exit();
    }

    if ($_POST["action"] == "delete") {
        $id = $_POST["id"];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["success" => true, "id" => $id]);
        exit();
    }
}

// Fetch students
$stmt = $conn->prepare("SELECT id, name, email, student_number FROM users WHERE role = 'student'");
$stmt->execute();
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensure proper scaling on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Modal Styling */
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        #editModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 400px;
            max-width: 95%;
        }
        #editModal h3 {
            margin-top: 0;
            color: #333;
        }
        #editModal label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        #editModal input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .modal-buttons button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        .save-btn {
            background: #28a745;
            color: white;
        }
        .close-btn {
            background: #6c757d;
            color: white;
        }
        .save-btn:hover {
            background: #218838;
        }
        .close-btn:hover {
            background: #5a6268;
        }
        /* Save Button (if used elsewhere) */
        #modal {
            background: #28a745; 
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        /* Close Button */
        #closeModal {
            background-color: #dc3545;
            color: white; 
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-edit { background-color: #ffc107; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .alert-msg {
            display: none;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }
        /* Responsive table container */
        .table-responsive {
            overflow-x: auto;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #2c3e50, #3498db);
        }
        .text-primary {
            color: #2c3e50 !important;
        }
        .card {
            border-radius: 0.5rem;
            border: none;
        }
        .shadow-hover {
            transition: all 0.3s ease;
        }
        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        #editModal {
            background: white;
            border-radius: 0.5rem;
        }
        #editModal input {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        #editModal input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        #Modal {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        #closeModal {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .table th {
            color: #2c3e50;
            font-weight: 600;
        }
        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
        }
        #studentSearch {
            border-radius: 0 0.5rem 0.5rem 0;
        }
        #studentSearch:focus {
            border-color: #3498db;
            box-shadow: none;
        }
        /* New Search Bar Styles */
        .search-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            max-width: 600px;
        }
        .search-box {
            width: 100%;
            position: relative;
            background: white;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 5px;
            transition: all 0.3s ease;
        }
        .search-box:hover, .search-box:focus-within {
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.15);
            transform: translateY(-2px);
        }
        .search-input {
            width: 100%;
            padding: 15px 25px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            color: #2c3e50;
            background: transparent;
            outline: none;
        }
        .search-input::placeholder {
            color: #95a5a6;
            font-weight: 400;
        }
        .search-button {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            margin-right: 5px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .search-button:hover {
            transform: rotate(90deg);
            background: linear-gradient(45deg, #3498db, #2c3e50);
        }
        .search-button i {
            font-size: 18px;
        }
        /* Optional: Add animation for the search icon */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .search-box:focus-within .search-button i {
            animation: pulse 1s infinite;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white p-4 rounded-3 shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="display-6 mb-1">Manage Students</h2>
                        <p class="lead mb-0">View and manage all student accounts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-wrapper">
                <div class="search-box">
                    <input type="text" id="studentSearch" class="search-input" placeholder="Search students...">
                    <button class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="student-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Student Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr id="row-<?php echo $student['id']; ?>">
                                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                                        <td class="name"><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td class="email"><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td class="student_number"><?php echo htmlspecialchars($student['student_number']); ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button class="btn btn-edit" 
                                                        data-id="<?php echo $student['id']; ?>" 
                                                        data-name="<?php echo $student['name']; ?>" 
                                                        data-email="<?php echo $student['email']; ?>" 
                                                        data-student_number="<?php echo $student['student_number']; ?>">
                                                    Edit
                                                </button>
                                                <button class="btn btn-delete" data-id="<?php echo $student['id']; ?>">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="overlay"></div>

<!-- Edit Modal -->
<div id="editModal">
    <h3>Edit Student</h3>
    <form id="edit-form">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="action" value="edit">
        
        <label>Name:</label>
        <input type="text" id="edit-name" name="name" required>

        <label>Email:</label>
        <input type="email" id="edit-email" name="email" required>

        <label>Student Number:</label>
        <input type="text" id="edit-student-number" name="student_number" required>

        <label>Password (Optional):</label>
        <input type="password" id="edit-password" name="password">

        <br><br>
        <button type="submit" id="Modal">Save Changes</button>
        <button type="button" id="closeModal">Close</button>
    </form>
</div>

<script>
$(document).ready(function () {
    // Edit button click: Show modal and populate fields
    $(".btn-edit").click(function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let email = $(this).data("email");
        let student_number = $(this).data("student_number");

        $("#edit-id").val(id);
        $("#edit-name").val(name);
        $("#edit-email").val(email);
        $("#edit-student-number").val(student_number);

        $("#overlay").show();
        $("#editModal").show();
    });

    // Close modal
    $("#closeModal").click(function () {
        $("#editModal").hide();
        $("#overlay").hide();
    });

    // Edit form submit (AJAX)
    $("#edit-form").submit(function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#row-" + response.id + " .name").text(response.name);
                    $("#row-" + response.id + " .email").text(response.email);
                    $("#row-" + response.id + " .student_number").text(response.student_number);
                    alert("Student updated successfully!");
                } else {
                    alert("Update failed!");
                }
                $("#editModal").hide();
                $("#overlay").hide();
            }
        });
    });

    // Delete button click (AJAX)
    $(".btn-delete").click(function () {
        let id = $(this).data("id");
        if (confirm("Are you sure you want to delete this student?")) {
            $.ajax({
                type: "POST",
                url: "",
                data: { action: "delete", id: id },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $("#row-" + response.id).remove();
                        alert("Student deleted successfully!");
                    } else {
                        alert("Deletion failed!");
                    }
                }
            });
        }
    });

    // Enhanced search functionality
    $("#studentSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#student-table tbody tr").each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });

        // Add no results message
        if($("#student-table tbody tr:visible").length === 0) {
            if($("#no-results").length === 0) {
                $("#student-table tbody").append(
                    '<tr id="no-results"><td colspan="5" class="text-center py-4">' +
                    '<div class="text-muted">' +
                    '<i class="fas fa-search mb-2" style="font-size: 24px;"></i>' +
                    '<p class="mb-0">No students found matching your search</p>' +
                    '</div></td></tr>'
                );
            }
        } else {
            $("#no-results").remove();
        }
    });

    // Optional: Clear search on ESC key
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            $("#studentSearch").val("").trigger("keyup");
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
