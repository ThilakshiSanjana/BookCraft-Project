<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}


$message = [];


$created_at_exists = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'created_at'");
$has_created_at = mysqli_num_rows($created_at_exists) > 0;


if(isset($_POST['add_user'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $password = mysqli_real_escape_string($conn, md5($_POST['password']));
   $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
   
   
   $check_email = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'");
   if(!$check_email) {
      $message[] = 'Email check query failed: ' . mysqli_error($conn);
   }
   else if(mysqli_num_rows($check_email) > 0){
      $message[] = 'Email already exists!';
   }else{
     
      if($has_created_at){
         $current_time = date('Y-m-d H:i:s');
         $query = "INSERT INTO `users` (name, email, password, user_type, created_at) 
                  VALUES ('$name', '$email', '$password', '$user_type', '$current_time')";
      } else {
         $query = "INSERT INTO `users` (name, email, password, user_type) 
                  VALUES ('$name', '$email', '$password', '$user_type')";
      }
      
      $result = mysqli_query($conn, $query);
      
      if(!$result) {
         $message[] = 'Failed to add user: ' . mysqli_error($conn);
      } else {
         $message[] = 'User added successfully!';
      }
   }
}

// Handle updating a user
if(isset($_POST['update_user'])){
   $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
   
   // MODIFIED: Check if status column exists before using it
   $status_column_exists = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'status'");
   $has_status = mysqli_num_rows($status_column_exists) > 0;
   
   if($has_status) {
      $status = mysqli_real_escape_string($conn, $_POST['status']);
      $query = "UPDATE `users` SET name = '$name', email = '$email', user_type = '$user_type', status = '$status' WHERE id = '$user_id'";
   } else {
      $query = "UPDATE `users` SET name = '$name', email = '$email', user_type = '$user_type' WHERE id = '$user_id'";
   }
   
   $result = mysqli_query($conn, $query);
   
   if(!$result) {
      $message[] = 'Failed to update user: ' . mysqli_error($conn);
   } else {
      // Update password if provided
      if(!empty($_POST['password'])){
         $password = mysqli_real_escape_string($conn, md5($_POST['password']));
         $pw_query = "UPDATE `users` SET password = '$password' WHERE id = '$user_id'";
         $pw_result = mysqli_query($conn, $pw_query);
         
         if(!$pw_result) {
            $message[] = 'Failed to update password: ' . mysqli_error($conn);
         }
      }
      
      $message[] = 'User updated successfully!';
   }
}

// Handle user deletion
if(isset($_GET['delete'])){
   $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
   $query = "DELETE FROM `users` WHERE id = '$delete_id'";
   $result = mysqli_query($conn, $query);
   
   if(!$result) {
      $message[] = 'Failed to delete user: ' . mysqli_error($conn);
   } else {
      $message[] = 'User deleted successfully!';
      // Redirect to the same page to avoid resubmission
      header('location: admin_users.php');
      exit();
   }
}

// MODIFIED: Check if status column exists before using it
$status_column_exists = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'status'");
$has_status = mysqli_num_rows($status_column_exists) > 0;

// Handle user status toggle only if status column exists
if($has_status && isset($_GET['status']) && isset($_GET['id'])){
   $user_id = mysqli_real_escape_string($conn, $_GET['id']);
   $status = $_GET['status'] == 'active' ? 'inactive' : 'active';
   
   $query = "UPDATE `users` SET status = '$status' WHERE id = '$user_id'";
   $result = mysqli_query($conn, $query);
   
   if(!$result) {
      $message[] = 'Failed to update status: ' . mysqli_error($conn);
   } else {
      $message[] = 'User status updated successfully!';
      // Redirect to the same page to avoid resubmission
      header('location: admin_users.php');
      exit();
   }
}

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_condition = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%'" : "";

// Filter by user type
$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';
if($filter) {
   $search_condition = $search_condition ? $search_condition . " AND user_type = '$filter'" : "WHERE user_type = '$filter'";
}

// Get total users for pagination
$total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM `users` $search_condition");
if(!$total_query) {
   $message[] = 'Count query failed: ' . mysqli_error($conn);
   $total_users = 0;
   $total_pages = 1;
} else {
   $total_users = mysqli_fetch_assoc($total_query)['count'];
   $total_pages = ceil($total_users / $results_per_page);
}

// Get users with pagination
$select_users_query = "SELECT * FROM `users` $search_condition ORDER BY id DESC LIMIT $start_from, $results_per_page";
$select_users = mysqli_query($conn, $select_users_query);

if(!$select_users) {
   $message[] = 'User query failed: ' . mysqli_error($conn) . ' Query: ' . $select_users_query;
}

// Get user counts for stats
$admin_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM `users` WHERE user_type = 'admin'");
$user_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM `users` WHERE user_type = 'user'");

$admin_count = $admin_query ? mysqli_fetch_assoc($admin_query)['count'] : 0;
$user_count = $user_query ? mysqli_fetch_assoc($user_query)['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Management | BookCraft Admin</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <!-- Custom admin CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">
   
   <style>
      :root {
         --primary: #6c5ce7;
         --secondary: #a29bfe;
         --dark: #2d3436;
         --light: #f9f9f9;
         --danger: #e74c3c;
         --success: #2ecc71;
         --warning: #f39c12;
         --info: #3498db;
      }
      
      .dashboard-container {
         padding: 30px;
         background-color: var(--light);
         border-radius: 10px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      }
      
      .user-card {
         background: white;
         border-radius: 10px;
         overflow: hidden;
         box-shadow: 0 4px 8px rgba(0,0,0,0.05);
         transition: transform 0.3s, box-shadow 0.3s;
         margin-bottom: 20px;
      }
      
      .user-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      }
      
      .user-card .card-header {
         padding: 15px;
         border-bottom: 1px solid #eee;
         background-color: #f8f9fa;
      }
      
      .user-card .badge {
         font-size: 12px;
         padding: 5px 10px;
      }
      
      .admin-badge {
         background-color: var(--primary) !important;
      }
      
      .pagination-container {
         margin: 30px 0;
      }
      
      .search-container {
         margin-bottom: 30px;
      }
      
      .user-stats {
         padding: 15px;
         background-color: white;
         border-radius: 10px;
         box-shadow: 0 4px 8px rgba(0,0,0,0.05);
         margin-bottom: 30px;
      }
      
      .stat-card {
         padding: 20px;
         text-align: center;
         background: linear-gradient(45deg, var(--primary), var(--secondary));
         color: white;
         border-radius: 10px;
         transition: transform 0.3s;
      }
      
      .stat-card:hover {
         transform: scale(1.05);
      }
      
      .action-btn {
         padding: 6px 12px;
         border-radius: 5px;
         margin-right: 5px;
         transition: all 0.3s;
      }
      
      .action-btn:hover {
         transform: translateY(-2px);
      }
      
      .alert-container {
         position: fixed;
         top: 20px;
         right: 20px;
         z-index: 9999;
      }
      
      .custom-alert {
         padding: 15px 20px;
         border-radius: 5px;
         margin-bottom: 10px;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
         animation: slideIn 0.5s forwards;
      }
      
      @keyframes slideIn {
         from {
            transform: translateX(100%);
            opacity: 0;
         }
         to {
            transform: translateX(0);
            opacity: 1;
         }
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<!-- Alert Messages -->
<div class="alert-container">
   <?php
   if(isset($message)){
      // Ensure $message is always an array
      $message_array = is_array($message) ? $message : [$message];
      
      foreach($message_array as $msg){
         $alert_type = strpos($msg, 'success') !== false ? 'success' : 'danger';
         echo '<div class="custom-alert alert alert-' . $alert_type . ' alert-dismissible fade show">
                  <i class="fas fa-' . ($alert_type == 'success' ? 'check-circle' : 'exclamation-circle') . ' me-2"></i>' . $msg . '
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>';
      }
   }
   ?>
</div>

<div class="container-fluid">
   <div class="dashboard-container">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h1 class="fw-bold text-primary"><i class="fas fa-users me-3"></i>User Management</h1>
         <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
           <i class="fas fa-plus me-2"></i>Add New User
         </button>
      </div>
      
      <!-- User Statistics -->
      <div class="row user-stats mb-4">
         <div class="col-md-3">
            <div class="stat-card bg-primary">
               <h3 class="mb-0"><?php echo $total_users; ?></h3>
               <p class="mb-0">Total Users</p>
            </div>
         </div>
         <div class="col-md-3">
            <div class="stat-card bg-success">
               <h3 class="mb-0"><?php echo $admin_count; ?></h3>
               <p class="mb-0">Administrators</p>
            </div>
         </div>
         <div class="col-md-3">
            <div class="stat-card bg-info">
               <h3 class="mb-0"><?php echo $user_count; ?></h3>
               <p class="mb-0">Customer</p>
            </div>
         </div>
         <div class="col-md-3">
            <div class="stat-card bg-warning">
               <h3 class="mb-0"><?php echo date('Y-m-d'); ?></h3>
               <p class="mb-0">Current Date</p>
            </div>
         </div>
      </div>
      
      <!-- Search and Filter -->
      <div class="row search-container">
         <div class="col-md-12">
            <form action="" method="GET" class="row g-3">
               <div class="col-md-4">
                  <div class="input-group">
                     <span class="input-group-text"><i class="fas fa-search"></i></span>
                     <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                  </div>
               </div>
               <div class="col-md-3">
                  <select name="filter" class="form-select">
                     <option value="">All User Types</option>
                     <option value="admin" <?php if($filter == 'admin') echo 'selected'; ?>>Administrators</option>
                     <option value="user" <?php if($filter == 'user') echo 'selected'; ?>>Customer</option>
                  </select>
               </div>
               <div class="col-md-2">
                  <button type="submit" class="btn btn-primary w-100">Filter</button>
               </div>
               <div class="col-md-2">
                  <a href="admin_users.php" class="btn btn-outline-secondary w-100">Reset</a>
               </div>
            </form>
         </div>
      </div>
      
      <!-- Users Table -->
      <div class="table-responsive">
         <table class="table table-hover">
            <thead class="table-light">
               <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>User Type</th>
                  <?php if($has_status): ?>
                  <th>Status</th>
                  <?php endif; ?>
                  <th>Joined</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($select_users && mysqli_num_rows($select_users) > 0) {
                  while($fetch_users = mysqli_fetch_assoc($select_users)) {
                     $status = ($has_status && isset($fetch_users['status'])) ? $fetch_users['status'] : 'active';
                     $joined_date = isset($fetch_users['created_at']) ? date('M d, Y', strtotime($fetch_users['created_at'])) : 'N/A';
               ?>
               <tr>
                  <td>#<?php echo $fetch_users['id']; ?></td>
                  <td>
                     <div class="d-flex align-items-center">
                        <div class="avatar me-2 bg-light rounded-circle text-center" style="width: 40px; height: 40px; line-height: 40px;">
                           <i class="fas fa-user text-secondary"></i>
                        </div>
                        <div>
                           <h6 class="mb-0"><?php echo htmlspecialchars($fetch_users['name']); ?></h6>
                        </div>
                     </div>
                  </td>
                  <td><?php echo htmlspecialchars($fetch_users['email']); ?></td>
                  <td>
                     <span class="badge <?php echo ($fetch_users['user_type'] == 'admin') ? 'admin-badge' : 'bg-secondary'; ?>">
                        <?php echo ucfirst($fetch_users['user_type']); ?>
                     </span>
                  </td>
                  <?php if($has_status): ?>
                  <td>
                     <span class="badge <?php echo ($status == 'active') ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo ucfirst($status); ?>
                     </span>
                  </td>
                  <?php endif; ?>
                  <td><?php echo $joined_date; ?></td>
                  <td>
                     <div class="d-flex">
                        <a href="#" class="action-btn btn btn-sm btn-info me-1" 
                           data-bs-toggle="modal" data-bs-target="#viewUserModal<?php echo $fetch_users['id']; ?>">
                           <i class="fas fa-eye"></i>
                        </a>
                        
                        <a href="#" class="action-btn btn btn-sm btn-warning me-1"
                           data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $fetch_users['id']; ?>">
                           <i class="fas fa-edit"></i>
                        </a>
                       
                        <a href="#" 
                           class="action-btn btn btn-sm btn-danger delete-btn" 
                           data-id="<?php echo $fetch_users['id']; ?>"
                           data-name="<?php echo htmlspecialchars($fetch_users['name']); ?>">
                           <i class="fas fa-trash"></i>
                        </a>
                     </div>
                  </td>
               </tr>
               
               <!-- View User Modal -->
               <div class="modal fade" id="viewUserModal<?php echo $fetch_users['id']; ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h5 class="modal-title">User Details</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                           <div class="text-center mb-4">
                              <div class="avatar bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                 <i class="fas fa-user fa-3x text-secondary"></i>
                              </div>
                              <h4><?php echo htmlspecialchars($fetch_users['name']); ?></h4>
                              <p class="text-muted"><?php echo htmlspecialchars($fetch_users['email']); ?></p>
                           </div>
                           <div class="row">
                              <div class="col-6 mb-3">
                                 <h6 class="text-muted">User ID</h6>
                                 <p>#<?php echo $fetch_users['id']; ?></p>
                              </div>
                              <div class="col-6 mb-3">
                                 <h6 class="text-muted">User Type</h6>
                                 <p><?php echo ucfirst($fetch_users['user_type']); ?></p>
                              </div>
                              <?php if($has_status): ?>
                              <div class="col-6 mb-3">
                                 <h6 class="text-muted">Status</h6>
                                 <p><?php echo ucfirst($status); ?></p>
                              </div>
                              <?php endif; ?>
                              <div class="col-6 mb-3">
                                 <h6 class="text-muted">Joined Date</h6>
                                 <p><?php echo $joined_date; ?></p>
                              </div>
                           </div>
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                           <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                              data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $fetch_users['id']; ?>">
                              Edit User
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
               
               <!-- Edit User Modal -->
               <div class="modal fade" id="editUserModal<?php echo $fetch_users['id']; ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h5 class="modal-title">Edit User</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="" method="post">
                           <div class="modal-body">
                              <input type="hidden" name="user_id" value="<?php echo $fetch_users['id']; ?>">
                              <div class="mb-3">
                                 <label class="form-label">Username</label>
                                 <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($fetch_users['name']); ?>" required>
                              </div>
                              <div class="mb-3">
                                 <label class="form-label">Email</label>
                                 <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($fetch_users['email']); ?>" required>
                              </div>
                              <div class="mb-3">
                                 <label class="form-label">User Type</label>
                                 <select class="form-select" name="user_type">
                                    <option value="user" <?php if($fetch_users['user_type'] == 'user') echo 'selected'; ?>>Customer</option>
                                    <option value="admin" <?php if($fetch_users['user_type'] == 'admin') echo 'selected'; ?>>Administrator</option>
                                 </select>
                              </div>
                              <?php if($has_status): ?>
                              <div class="mb-3">
                                 <label class="form-label">Status</label>
                                 <select class="form-select" name="status">
                                    <option value="active" <?php if($status == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="inactive" <?php if($status == 'inactive') echo 'selected'; ?>>Inactive</option>
                                 </select>
                              </div>
                              <?php endif; ?>
                              <div class="mb-3">
                                 <label class="form-label">New Password (leave blank to keep current)</label>
                                 <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="editPassword<?php echo $fetch_users['id']; ?>">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="editPassword<?php echo $fetch_users['id']; ?>">
                                       <i class="fas fa-eye"></i>
                                    </button>
                                 </div>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
               <?php
                  }
               } else {
                  echo '<tr><td colspan="7" class="text-center py-5"><i class="fas fa-users fa-3x mb-3 text-secondary"></i><p>No users found</p></td></tr>';
               }
               ?>
            </tbody>
         </table>
      </div>
      
      <!-- Pagination -->
      <?php if($total_pages > 1): ?>
      <nav aria-label="Page navigation" class="pagination-container">
         <ul class="pagination justify-content-center">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
               <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?page=".($page-1); if($search) echo "&search=$search"; if($filter) echo "&filter=$filter"; } ?>">
                  <i class="fas fa-chevron-left"></i>
               </a>
            </li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if($i == $page) echo 'active'; ?>">
               <a class="page-link" href="?page=<?php echo $i; if($search) echo "&search=$search"; if($filter) echo "&filter=$filter"; ?>">
                  <?php echo $i; ?>
               </a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
               <a class="page-link" href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?page=".($page+1); if($search) echo "&search=$search"; if($filter) echo "&filter=$filter"; } ?>">
                  <i class="fas fa-chevron-right"></i>
               </a>
            </li>
         </ul>
      </nav>
      <?php endif; ?>
   </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Add New User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="" method="post" id="addUserForm">
            <div class="modal-body">
               <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" class="form-control" name="name" required>
               </div>
               <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" required>
               </div>
               <div class="mb-3">
                  <label class="form-label">Password</label>
                  <div class="input-group">
                     <input type="password" class="form-control" name="password" id="addPassword" required>
                     <button class="btn btn-outline-secondary toggle-password" type="button" data-target="addPassword">
                        <i class="fas fa-eye"></i>
                     </button>
                  </div>
               </div>
               <div class="mb-3">
                  <label class="form-label">User Type</label>
                  <select class="form-select" name="user_type">
                     <option value="user">Customer</option>
                     <option value="admin">Administrator</option>
                  </select>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title text-danger">Delete User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center">
            <i class="fas fa-exclamation-triangle text-warning fa-4x mb-3"></i>
            <h4>Are you sure you want to delete this user?</h4>
            <p class="text-muted user-name-placeholder"></p>
            <p class="text-danger">This action cannot be undone!</p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <a href="#" class="btn btn-danger delete-confirm-btn">Delete User</a>
         </div>
      </div>
   </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom admin JS file link -->
<script src="js/admin_script.js"></script>

<script>
   // Delete confirmation
   document.addEventListener('DOMContentLoaded', function() {
      const deleteButtons = document.querySelectorAll('.delete-btn');
      const deleteConfirmBtn = document.querySelector('.delete-confirm-btn');
      const userNamePlaceholder = document.querySelector('.user-name-placeholder');
      
      deleteButtons.forEach(button => {
         button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            userNamePlaceholder.textContent = userName;
            deleteConfirmBtn.href = 'admin_users.php?delete=' + userId;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
         });
      });
      
      // Auto-dismiss alerts after 5 seconds
      setTimeout(() => {
         const alerts = document.querySelectorAll('.alert');
         alerts.forEach(alert => {
            try {
               // Try to use Bootstrap's dismissal
               const closeBtn = alert.querySelector('.btn-close');
               if (closeBtn) {
                  closeBtn.click();
               }
            } catch (e) {
               // Fallback to manual removal
               alert.style.opacity = '0';
               setTimeout(() => {
                  alert.remove();
               }, 500);
            }
         });
      }, 5000);
      
      // Form validation for add user
      const addUserForm = document.getElementById('addUserForm');
      if (addUserForm) {
         addUserForm.addEventListener('submit', function(e) {
            const nameInput = this.querySelector('input[name="name"]');
            const emailInput = this.querySelector('input[name="email"]');
            const passwordInput = this.querySelector('input[name="password"]');
            
            if (!nameInput.value.trim()) {
               e.preventDefault();
               alert('Username is required');
               nameInput.focus();
               return false;
            }
            
            if (!emailInput.value.trim()) {
               e.preventDefault();
               alert('Email is required');
               emailInput.focus();
               return false;
            }
            
            if (!passwordInput.value.trim()) {
               e.preventDefault();
               alert('Password is required');
               passwordInput.focus();
               return false;
            }
            
            return true;
         });
      }
      
      // Password toggle functionality
      const togglePasswordButtons = document.querySelectorAll('.toggle-password');
      togglePasswordButtons.forEach(button => {
         button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            // Toggle password visibility
            if (passwordInput.type === 'password') {
               passwordInput.type = 'text';
               icon.classList.remove('fa-eye');
               icon.classList.add('fa-eye-slash');
            } else {
               passwordInput.type = 'password';
               icon.classList.remove('fa-eye-slash');
               icon.classList.add('fa-eye');
            }
         });
      });
   });
</script>

</body>
</html>