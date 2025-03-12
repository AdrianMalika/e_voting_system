<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/notifications.php';

ob_start();

$now = date('Y-m-d H:i:s');

$unread_notifications = 0;
if (isset($user) && $user['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE");
    $stmt->execute();
    $unread_notifications = $stmt->fetchColumn();
}

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $conn->prepare("
            SELECT id, title 
            FROM elections 
            WHERE status != 'completed' 
            AND end_date < ? 
            AND (manual_status = 0 OR manual_status IS NULL)
        ");
        $stmt->execute([$now]);
        $ended_elections = $stmt->fetchAll();

        $stmt = $conn->prepare("
            UPDATE elections 
            SET status = 
                CASE 
                    WHEN start_date > ? THEN 'upcoming'
                    WHEN end_date < ? THEN 'completed'
                    ELSE 'active'
                END,
                updated_at = NOW()
            WHERE manual_status = 0 OR manual_status IS NULL
        ");
        $stmt->execute([$now, $now]);

        foreach ($ended_elections as $election) {
            $message = "Election '" . htmlspecialchars($election['title']) . "' has ended";
            add_notification($conn, 'election_ended', $message, $election['id'], 'election');
        }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MCHS-E-Voting System</title>
  <link rel="icon" href="https://mchs.mw/img/mchs_logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
      :root {
          --primary-color: #2c3e50;
          --secondary-color: #3498db;
          --accent-color: #e74c3c;
          --text-light: #ecf0f1;
          --success-color: #2ecc71;
      }
      /* Sidebar styles (static on large screens) */
      .sidebar {
          background: var(--primary-color);
          width: 250px;
          padding: 20px 0;
          color: var(--text-light);
          transition: all 0.3s ease;
      }
      .sidebar-header {
          padding: 20px;
          text-align: center;
          border-bottom: 1px solid rgba(255,255,255,0.1);
      }
      .sidebar-header img {
          width: 80px;
          height: 80px;
          border-radius: 50%;
          margin-bottom: 10px;
      }
      .user-info {
          padding: 15px;
          text-align: center;
          background: rgba(255,255,255,0.05);
          margin: 10px 15px;
          border-radius: 10px;
      }
      .nav-menu {
          padding: 0;
          margin: 20px 0;
      }
      .nav-item {
          list-style: none;
          margin: 5px 15px;
      }
      .nav-link {
          color: var(--text-light) !important;
          padding: 12px 20px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          transition: all 0.3s ease;
          text-decoration: none;
          opacity: 0.8;
      }
      .nav-link:hover,
      .nav-link.active {
          background: var(--secondary-color);
          opacity: 1;
      }
      .nav-link i {
          margin-right: 10px;
          width: 20px;
          text-align: center;
      }
      /* Main Content styles */
      .main-content {
          padding: 20px;
          background: #f8f9fa;
          transition: all 0.3s ease;
          min-height: 100vh;
      }
      /* Fixed Sidebar on large screens */
      @media (min-width: 992px) {
          .sidebar {
              position: fixed;
              top: 0;
              left: 0;
              height: 100vh;
              overflow-y: auto;
          }
          .main-content {
              margin-left: 250px;
          }
      }
      /* Offcanvas Sidebar styling for mobile to match static sidebar */
      .offcanvas.offcanvas-start {
          background: var(--primary-color);
          color: var(--text-light);
      }
      .offcanvas.offcanvas-start .offcanvas-header {
          background: var(--primary-color);
          border-bottom: 1px solid rgba(255,255,255,0.1);
      }
      .offcanvas.offcanvas-start .offcanvas-title {
          color: var(--text-light);
      }
  </style>
</head>
<body>
  <!-- Top Navbar for Mobile -->
  <nav class="navbar navbar-light bg-light d-lg-none">
    <div class="container-fluid">
      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
        <i class="fas fa-bars"></i>
      </button>
      <a class="navbar-brand" href="#">MCHS-E-Voting System</a>
    </div>
  </nav>

  <!-- Offcanvas Sidebar for Mobile -->
  <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="mobileSidebarLabel">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
      <?php include 'sidebar-content.php'; ?>
    </div>
  </div>

  <!-- Static Sidebar for Large Screens -->
  <div class="d-none d-lg-block">
      <div class="sidebar">
        <?php include 'sidebar-content.php'; ?>
      </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
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

    <!-- Page content begins here -->
