<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS e_voting_system");
    $conn->exec("USE e_voting_system");

    // Users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        student_number VARCHAR(20) UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('student', 'admin', 'candidate') NOT NULL DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Candidates table
    $conn->exec("CREATE TABLE IF NOT EXISTS candidates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        position VARCHAR(100) NOT NULL,
        manifesto TEXT,
        photo_url VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Elections table
    $conn->exec("CREATE TABLE IF NOT EXISTS elections (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");

    // Election Candidates table
    $conn->exec("CREATE TABLE IF NOT EXISTS election_candidates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        election_id INT NOT NULL,
        candidate_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id),
        FOREIGN KEY (candidate_id) REFERENCES candidates(id),
        UNIQUE KEY unique_election_candidate (election_id, candidate_id)
    )");

    // Votes table
    $conn->exec("CREATE TABLE IF NOT EXISTS votes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        election_id INT NOT NULL,
        voter_id INT NOT NULL,
        candidate_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id),
        FOREIGN KEY (voter_id) REFERENCES users(id),
        FOREIGN KEY (candidate_id) REFERENCES candidates(id),
        UNIQUE KEY unique_vote (election_id, voter_id)
    )");

    // Audit logs table
    $conn->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Check if admin exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;

    // Create default admin if none exists
    if (!$adminExists) {
        $admin_email = "admin@evoting.com";
        $admin_password = "admin123"; // You should change this immediately after first login
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password_hash, role) 
            VALUES ('System Admin', ?, ?, 'admin')
        ");
        $stmt->execute([$admin_email, $password_hash]);

        echo "Default admin account created!\n";
        echo "Email: admin@evoting.com\n";
        echo "Password: admin123\n";
        echo "IMPORTANT: Please change this password immediately after first login!\n";
    }

    echo "Database and tables created successfully!";
} catch(PDOException $e) {
    die("Error creating database and tables: " . $e->getMessage());
}
?>
