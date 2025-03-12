<div class="sidebar-header">
  <img src="https://mchs.mw/img/mchs_logo.png" alt="MCHS Logo" class="img-fluid" style="width: 50%;">
  <h6 class="mt-0">MCHS : E-Voting System</h6>
</div>

<?php if (isset($user)): ?>
  <div class="user-info">
    <i class="fas fa-user-circle fa-1x mb-0"></i>
    <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
    <small><?php echo ucfirst($user['role']); ?></small>
  </div>

  <ul class="nav-menu">
    <?php if ($user['role'] === 'admin'): ?>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/admin/dashboard.php">
          <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'notifications.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/admin/notifications.php">
          <i class="fas fa-bell me-2"></i>Notifications
          <?php 
          $unread_count = get_unread_notifications_count($conn);
          if ($unread_count > 0): 
          ?>
            <span class="badge bg-danger rounded-pill">
              <?php echo $unread_count; ?>
            </span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'manage_elections.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/admin/manage_elections.php">
          <i class="fas fa-poll me-2"></i>Manage Elections
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'add_student.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/admin/add_student.php">
          <i class="fas fa-user-plus me-2"></i>Add User
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'manage_students.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/admin/manage_students.php">
          <i class="fas fa-users-cog me-2"></i>Manage Student
        </a>
      </li>

    <?php elseif ($user['role'] === 'candidate'): ?>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/candidate/dashboard.php">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/candidate/profile.php">
          <i class="fas fa-user"></i>
          <span>Profile</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'view_results.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/candidate/view_results.php">
          <i class="fas fa-chart-bar"></i>
          <span>Results</span>
        </a>
      </li>
    <?php elseif ($user['role'] === 'student'): ?>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/student/dashboard.php">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'elections.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/student/elections.php">
          <i class="fas fa-vote-yea"></i>
          <span>Elections</span>
        </a>
      </li>
      <li class="nav-item">


      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'notifications.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/student/notifications.php">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
          <?php 
          $unread_count = get_unread_notifications_count($conn, $_SESSION['user_id']);
          if ($unread_count > 0): 
          ?>
            <span class="badge bg-danger rounded-pill ms-2">
              <?php echo $unread_count; ?>
            </span>
          <?php endif; ?>
        </a>
      </li>

      <li class="nav-item">
    <a class="nav-link <?php echo ($current_page === 'nomination_form.php') ? 'active' : ''; ?>" 
       href="/e_voting_system/student/nomination_form.php">
        <i class="fas fa-user-check"></i>
        <span>Apply</span>
    </a>
</li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'vote.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/student/vote.php">
          <i class="fas fa-check-square"></i>
          <span>Vote</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $current_page === 'view_results.php' ? 'active' : ''; ?>" 
           href="/e_voting_system/student/view_results.php">
          <i class="fas fa-chart-bar"></i>
          <span>Results</span>
        </a>
      </li>
    <?php endif; ?>

    <li class="nav-item mt-4">
      <a class="nav-link" href="/e_voting_system/logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
<?php endif; ?>
