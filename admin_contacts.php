<?php

include 'config.php';
session_start();

// Check if admin is logged in
$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

// Add status column to message table if it doesn't exist
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM `message` LIKE 'status'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($conn, "ALTER TABLE `message` ADD `status` VARCHAR(20) DEFAULT NULL");
}

// Delete message with error handling
if(isset($_GET['delete'])){
   $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
   $delete_query = mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'");
   
   if(!$delete_query) {
      $error_message = "Delete operation failed: " . mysqli_error($conn);
   } else {
      header('location:admin_contacts.php');
      exit();
   }
}

// Mark message as read with error handling
if(isset($_GET['mark_read'])){
   $message_id = mysqli_real_escape_string($conn, $_GET['mark_read']);
   $mark_query = mysqli_query($conn, "UPDATE `message` SET status = 'read' WHERE id = '$message_id'");
   
   if(!$mark_query) {
      $error_message = "Mark as read operation failed: " . mysqli_error($conn);
   } else {
      header('location:admin_contacts.php');
      exit();
   }
}

// Mark all as read with error handling
if(isset($_GET['mark_all_read'])){
   $mark_all_query = mysqli_query($conn, "UPDATE `message` SET status = 'read' WHERE status IS NULL OR status != 'read'");
   
   if(!$mark_all_query) {
      $error_message = "Mark all as read operation failed: " . mysqli_error($conn);
   } else {
      header('location:admin_contacts.php');
      exit();
   }
}

// Filter settings
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = '';
if($filter === 'read') {
   $filter_condition = " WHERE status = 'read'";
} else if($filter === 'unread') {
   $filter_condition = " WHERE status IS NULL OR status != 'read'";
}

// Pagination settings
$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Search functionality with error handling
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_condition = '';
if(!empty($search)) {
    if(!empty($filter_condition)) {
        $search_condition = " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR message LIKE '%$search%')";
    } else {
        $search_condition = " WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR message LIKE '%$search%'";
    }
}

// Count total messages for pagination with error handling
$count_query_sql = "SELECT COUNT(*) as total FROM `message`" . $filter_condition . $search_condition;
$count_result = mysqli_query($conn, $count_query_sql);

if(!$count_result) {
    $error_message = "Count query failed: " . mysqli_error($conn);
    $total_messages = 0;
    $total_pages = 0;
} else {
    $count_row = mysqli_fetch_assoc($count_result);
    $total_messages = $count_row['total'];
    $total_pages = ceil($total_messages / $results_per_page);
}

// Count unread messages with error handling
$unread_query_sql = "SELECT COUNT(*) as unread FROM `message` WHERE status IS NULL OR status != 'read'";
$unread_result = mysqli_query($conn, $unread_query_sql);

if(!$unread_result) {
    $error_message = "Unread count query failed: " . mysqli_error($conn);
    $unread_messages = 0;
} else {
    $unread_row = mysqli_fetch_assoc($unread_result);
    $unread_messages = $unread_row['unread'];
}

// Get messages with filter, search and pagination with error handling
$select_query_sql = "SELECT * FROM `message`" . 
    $filter_condition . 
    $search_condition . 
    " ORDER BY id DESC LIMIT $start_from, $results_per_page";

$select_message = mysqli_query($conn, $select_query_sql);

if(!$select_message) {
    $error_message = "Select query failed: " . mysqli_error($conn) . "<br>SQL: " . $select_query_sql;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Messages | BookCraft</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <!-- Custom admin CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">
   
   <style>
      :root {
         --primary-color: #4e73df;
         --secondary-color: #858796;
         --success-color: #1cc88a;
         --info-color: #36b9cc;
         --warning-color: #f6c23e;
         --danger-color: #e74a3b;
         --light-color: #f8f9fc;
         --dark-color: #5a5c69;
      }
      
      body {
         background-color: #f8f9fc;
      }
      
      .dashboard-container {
         padding: 20px;
         max-width: 1200px;
         margin: 0 auto;
      }
      
      .page-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 25px;
         border-bottom: 2px solid #e3e6f0;
         padding-bottom: 15px;
      }
      
      @media (max-width: 768px) {
         .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
         }
         
         .search-container {
            width: 100%;
            max-width: 100% !important;
         }
      }
      
      .message-card {
         background-color: #fff;
         border-radius: 10px;
         box-shadow: 0 4px 6px rgba(0,0,0,0.1);
         margin-bottom: 20px;
         padding: 20px;
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
         border: 1px solid transparent;
      }
      
      .message-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 6px 12px rgba(0,0,0,0.15);
         border-color: #e3e6f0;
      }
      
      .message-card.unread {
         border-left: 4px solid var(--primary-color);
      }
      
      .message-card.read {
         border-left: 4px solid var(--success-color);
      }
      
      .message-header {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;
         margin-bottom: 15px;
         padding-bottom: 10px;
         border-bottom: 1px solid #e3e6f0;
      }
      
      .message-content {
         background-color: var(--light-color);
         border-radius: 8px;
         padding: 15px;
         margin-bottom: 15px;
         max-height: 150px;
         overflow-y: auto;
         line-height: 1.6;
      }
      
      .message-content::-webkit-scrollbar {
         width: 8px;
      }
      
      .message-content::-webkit-scrollbar-track {
         background: #f1f1f1;
         border-radius: 8px;
      }
      
      .message-content::-webkit-scrollbar-thumb {
         background: #c1c1c1;
         border-radius: 8px;
      }
      
      .message-content::-webkit-scrollbar-thumb:hover {
         background: #a1a1a1;
      }
      
      .message-actions {
         display: flex;
         justify-content: flex-end;
         gap: 10px;
         flex-wrap: wrap;
      }
      
      .message-info {
         display: flex;
         flex-wrap: wrap;
         gap: 12px;
         margin-bottom: 15px;
      }
      
      .message-info-item {
         background-color: #f1f3f9;
         padding: 6px 12px;
         border-radius: 20px;
         font-size: 14px;
         display: inline-flex;
         align-items: center;
         transition: all 0.2s ease;
      }
      
      .message-info-item:hover {
         background-color: #e3e6f0;
         box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      }
      
      .message-info-item i {
         margin-right: 6px;
         color: var(--primary-color);
      }
      
      .status-badge {
         font-size: 12px;
         padding: 4px 10px;
         border-radius: 20px;
         font-weight: 600;
      }
      
      .status-read {
         background-color: var(--success-color);
         color: white;
      }
      
      .status-unread {
         background-color: var(--primary-color);
         color: white;
      }
      
      .empty-state {
         text-align: center;
         padding: 60px 20px;
         background-color: #fff;
         border-radius: 10px;
         border: 1px dashed #d1d3e2;
         box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      }
      
      .empty-state i {
         font-size: 48px;
         color: #d1d3e2;
         margin-bottom: 20px;
      }
      
      .search-container {
         position: relative;
         max-width: 400px;
      }
      
      .search-container button {
         position: absolute;
         right: 5px;
         top: 5px;
         background: none;
         border: none;
         color: var(--primary-color);
         cursor: pointer;
         transition: color 0.2s ease;
      }
      
      .search-container button:hover {
         color: var(--dark-color);
      }
      
      .search-input {
         padding-right: 40px;
         border-radius: 20px;
         border: 1px solid #d1d3e2;
         transition: all 0.3s ease;
      }
      
      .search-input:focus {
         box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
         border-color: #bac8f3;
      }
      
      /* Modal styles */
      .custom-modal {
         display: none;
         position: fixed;
         z-index: 1000;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0,0,0,0.5);
         animation: fadeIn 0.3s ease;
      }
      
      @keyframes fadeIn {
         from { opacity: 0; }
         to { opacity: 1; }
      }
      
      .modal-content {
         background-color: #fff;
         margin: 10% auto;
         padding: 25px;
         border-radius: 10px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.2);
         width: 500px;
         max-width: 90%;
         animation: slideIn 0.3s ease;
         position: relative;
      }
      
      @keyframes slideIn {
         from { transform: translateY(-50px); opacity: 0; }
         to { transform: translateY(0); opacity: 1; }
      }
      
      .modal-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         border-bottom: 1px solid #e3e6f0;
         padding-bottom: 15px;
         margin-bottom: 15px;
      }
      
      .modal-actions {
         display: flex;
         justify-content: flex-end;
         gap: 10px;
         margin-top: 20px;
      }
      
      .pagination-container {
         display: flex;
         justify-content: center;
         margin-top: 30px;
      }
      
      .pagination .page-link {
         color: var(--primary-color);
         border-radius: 5px;
         margin: 0 3px;
      }
      
      .pagination .page-item.active .page-link {
         background-color: var(--primary-color);
         border-color: var(--primary-color);
      }
      
      .stats-container {
         display: flex;
         gap: 15px;
         margin-bottom: 25px;
         flex-wrap: wrap;
      }
      
      .stat-card {
         flex: 1;
         min-width: 200px;
         background: #fff;
         border-radius: 10px;
         box-shadow: 0 4px 6px rgba(18, 16, 16, 0.99);
         padding: 15px;
         display: flex;
         align-items: center;
         transition: all 0.3s ease;
         border-left: 4px solid;
      }
      
      .stat-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 6px 12px rgba(247, 241, 241, 0.15);
      }
      
      .stat-card.primary {
         border-left-color: var(--primary-color);
      }
      
      .stat-card.success {
         border-left-color: var(--success-color);
      }
      
      .stat-card.warning {
         border-left-color: var(--warning-color);
      }
      
      .stat-card-icon {
         font-size: 24px;
         width: 60px;
         height: 60px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-right: 15px;
         color: white;
      }
      
      .primary-icon {
         background-color: var(--primary-color);
      }
      
      .success-icon {
         background-color: var(--success-color);
      }
      
      .warning-icon {
         background-color: var(--warning-color);
      }
      
      .stat-card-content {
         flex: 1;
      }
      
      .stat-card-title {
         color: var(--secondary-color);
         font-size: 13px;
         font-weight: 600;
         text-transform: uppercase;
         margin-bottom: 5px;
      }
      
      .stat-card-value {
         font-size: 20px;
         font-weight: 700;
         color: var(--dark-color);
         margin-bottom: 0;
      }
      
      .filter-container {
         display: flex;
         gap: 10px;
         margin-bottom: 20px;
         flex-wrap: wrap;
      }
      
      .filter-btn {
         padding: 8px 16px;
         border-radius: 20px;
         font-size: 14px;
         font-weight: 500;
         background-color: white;
         border: 1px solid #d1d3e2;
         transition: all 0.2s ease;
         cursor: pointer;
      }
      
      .filter-btn:hover {
         background-color: #f8f9fc;
         border-color: var(--primary-color);
      }
      
      .filter-btn.active {
         background-color: var(--primary-color);
         color: white;
         border-color: var(--primary-color);
      }
      
      .message-card-date {
         font-size: 13px;
         color: var(--secondary-color);
      }
      
      .tooltip-container {
         position: relative;
         display: inline-block;
      }
      
      .tooltip-text {
         visibility: hidden;
         width: 120px;
         background-color: #333;
         color: #fff;
         text-align: center;
         border-radius: 6px;
         padding: 5px;
         position: absolute;
         z-index: 1;
         bottom: 125%;
         left: 50%;
         margin-left: -60px;
         opacity: 0;
         transition: opacity 0.3s;
         font-size: 12px;
      }
      
      .tooltip-container:hover .tooltip-text {
         visibility: visible;
         opacity: 1;
      }
      
      .btn-with-icon {
         display: inline-flex;
         align-items: center;
         gap: 5px;
      }
      
      .btn-with-icon i {
         font-size: 14px;
      }
      
      /* Loading spinner */
      .loader {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(255,255,255,0.7);
         z-index: 9999;
         justify-content: center;
         align-items: center;
      }
      
      .spinner {
         width: 40px;
         height: 40px;
         border: 4px solid rgba(0,0,0,0.1);
         border-radius: 50%;
         border-top: 4px solid var(--primary-color);
         animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
         0% { transform: rotate(0deg); }
         100% { transform: rotate(360deg); }
      }
      
      /* Dark mode toggle */
      .theme-toggle {
         position: fixed;
         bottom: 20px;
         right: 20px;
         z-index: 99;
         width: 45px;
         height: 45px;
         border-radius: 50%;
         background: var(--primary-color);
         color: white;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         box-shadow: 0 4px 6px rgba(0,0,0,0.1);
         transition: all 0.3s ease;
      }
      
      .theme-toggle:hover {
         transform: translateY(-5px);
         box-shadow: 0 6px 12px rgba(0,0,0,0.15);
      }
      
      /* Error message styles */
      .sql-error {
         background-color: #ffeaea;
         border-left: 4px solid var(--danger-color);
         padding: 15px;
         margin-bottom: 20px;
         border-radius: 4px;
      }
      
      .sql-error pre {
         margin-top: 10px;
         background-color: #fff;
         padding: 10px;
         border-radius: 4px;
         overflow-x: auto;
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<!-- Loading spinner -->
<div class="loader">
   <div class="spinner"></div>
</div>

<div class="dashboard-container">
   <div class="page-header">
      <h1 class="h3 text-gray-800">Customer Messages</h1>
      
      <!-- Search box -->
      <div class="search-container">
         <form action="" method="GET" id="searchForm">
            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            <input type="text" name="search" class="form-control search-input" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
         </form>
      </div>
   </div>

   <?php if(isset($error_message)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong><i class="fas fa-exclamation-triangle me-2"></i>Database Error:</strong> 
      <?php echo $error_message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   </div>
   <?php endif; ?>

   <?php if(!empty($search)): ?>
   <div class="alert alert-info mb-4 d-flex justify-content-between align-items-center">
      <div>
         <i class="fas fa-search me-2"></i> Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong>
      </div>
      <a href="admin_contacts.php<?php echo $filter !== 'all' ? '?filter=' . $filter : ''; ?>" class="btn btn-sm btn-outline-primary">Clear search</a>
   </div>
   <?php endif; ?>

   <!-- Message stats -->
   <div class="stats-container">
      <div class="stat-card primary">
         <div class="stat-card-icon primary-icon">
            <i class="fas fa-envelope"></i>
         </div>
         <div class="stat-card-content">
            <div class="stat-card-title">Total Messages</div>
            <div class="stat-card-value"><?php echo $total_messages; ?></div>
         </div>
      </div>
      
      <div class="stat-card warning">
         <div class="stat-card-icon warning-icon">
            <i class="fas fa-bell"></i>
         </div>
         <div class="stat-card-content">
            <div class="stat-card-title">Unread Messages</div>
            <div class="stat-card-value"><?php echo $unread_messages; ?></div>
         </div>
      </div>
      
      <div class="stat-card success">
         <div class="stat-card-icon success-icon">
            <i class="fas fa-check-circle"></i>
         </div>
         <div class="stat-card-content">
            <div class="stat-card-title">Read Messages</div>
            <div class="stat-card-value"><?php echo $total_messages - $unread_messages; ?></div>
         </div>
      </div>
   </div>

   <!-- Filter buttons -->
   <div class="filter-container">
      <a href="admin_contacts.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
         <i class="fas fa-list me-1"></i> All Messages
      </a>
      <a href="admin_contacts.php?filter=unread<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">
         <i class="fas fa-envelope me-1"></i> Unread
      </a>
      <a href="admin_contacts.php?filter=read<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
         <i class="fas fa-check-circle me-1"></i> Read
      </a>
      
      <?php if($unread_messages > 0): ?>
      <a href="admin_contacts.php?mark_all_read" class="filter-btn ms-auto btn-with-icon" onclick="return confirm('Mark all messages as read?');">
         <i class="fas fa-check-double"></i> Mark All as Read
      </a>
      <?php endif; ?>
   </div>

   <!-- Messages container -->
   <div class="row">
      <div class="col-12">
         <?php if(isset($select_message) && mysqli_num_rows($select_message) > 0): ?>
            <?php $delay = 0; while($fetch_message = mysqli_fetch_assoc($select_message)): $delay += 0.1; ?>
               <div class="message-card <?php echo isset($fetch_message['status']) && $fetch_message['status'] === 'read' ? 'read' : 'unread'; ?> animated-card" style="animation-delay: <?php echo $delay; ?>s;">
                  <div class="message-header">
                     <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($fetch_message['name'] ?? 'Unknown'); ?></h5>
                        <span class="message-card-date">
                           <i class="far fa-clock me-1"></i>
                           <?php 
                           // Display date if available, otherwise show current date
                           echo isset($fetch_message['date']) ? date('F j, Y, g:i a', strtotime($fetch_message['date'])) : date('F j, Y, g:i a'); 
                           ?>
                        </span>
                     </div>
                     <span class="status-badge <?php echo isset($fetch_message['status']) && $fetch_message['status'] === 'read' ? 'status-read' : 'status-unread'; ?>">
                        <?php echo isset($fetch_message['status']) && $fetch_message['status'] === 'read' ? 'Read' : 'New'; ?>
                     </span>
                  </div>
                  
                  <div class="message-info">
                     <div class="message-info-item">
                        <i class="fas fa-user"></i> 
                        User ID: <?php echo htmlspecialchars($fetch_message['user_id'] ?? 'Guest'); ?>
                     </div>
                     <div class="message-info-item">
                        <i class="fas fa-envelope"></i> 
                        <?php echo htmlspecialchars($fetch_message['email'] ?? 'No email'); ?>
                     </div>
                     <?php if(isset($fetch_message['number']) && !empty($fetch_message['number'])): ?>
                     <div class="message-info-item">
                        <i class="fas fa-phone"></i> 
                        <?php echo htmlspecialchars($fetch_message['number']); ?>
                     </div>
                     <?php endif; ?>
                  </div>
                  
                  <div class="message-content">
                     <p><?php echo nl2br(htmlspecialchars($fetch_message['message'] ?? 'No message content')); ?></p>
                  </div>
                  
                  <div class="message-actions">
                     <?php if(!isset($fetch_message['status']) || $fetch_message['status'] !== 'read'): ?>
                        <a href="admin_contacts.php?mark_read=<?php echo $fetch_message['id']; ?>" class="btn btn-success btn-sm btn-with-icon">
                           <i class="fas fa-check-circle"></i> Mark as Read
                        </a>
                     <?php endif; ?>
                     <button class="btn btn-primary btn-sm btn-with-icon reply-btn" 
                        data-email="<?php echo htmlspecialchars($fetch_message['email'] ?? ''); ?>" 
                        data-name="<?php echo htmlspecialchars($fetch_message['name'] ?? 'Customer'); ?>">
                        <i class="fas fa-reply"></i> Reply
                     </button>
                     <button class="btn btn-danger btn-sm btn-with-icon delete-btn" data-id="<?php echo $fetch_message['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Delete
                     </button>
                  </div>
               </div>
            <?php endwhile; ?>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
               <div class="pagination-container">
                  <nav aria-label="Page navigation">
                     <ul class="pagination">
                        <?php if($page > 1): ?>
                           <li class="page-item">
                              <a class="page-link" href="?page=<?php echo $page-1; echo !empty($search) ? '&search='.$search : ''; echo $filter !== 'all' ? '&filter='.$filter : ''; ?>" aria-label="Previous">
                                 <span aria-hidden="true">&laquo;</span>
                              </a>
                           </li>
                        <?php endif; ?>
                        
                        <?php 
                        // Show limited page numbers with ellipsis
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                           echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search='.$search : '') . ($filter !== 'all' ? '&filter='.$filter : '') . '">1</a></li>';
                           if ($start_page > 2) {
                              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                           }
                        }
                        
                        for($i = $start_page; $i <= $end_page; $i++): ?>
                           <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                              <a class="page-link" href="?page=<?php echo $i; echo !empty($search) ? '&search='.$search : ''; echo $filter !== 'all' ? '&filter='.$filter : ''; ?>">
                                 <?php echo $i; ?>
                              </a>
                           </li>
                        <?php endfor;
                        
                        if ($end_page < $total_pages) {
                           if ($end_page < $total_pages - 1) {
                              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                           }
                           echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search='.$search : '') . ($filter !== 'all' ? '&filter='.$filter : '') . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <?php if($page < $total_pages): ?>
                           <li class="page-item">
                              <a class="page-link" href="?page=<?php echo $page+1; echo !empty($search) ? '&search='.$search : ''; echo $filter !== 'all' ? '&filter='.$filter : ''; ?>" aria-label="Next">
                                 <span aria-hidden="true">&raquo;</span>
                              </a>
                           </li>
                        <?php endif; ?>
                     </ul>
                  </nav>
               </div>
            <?php endif; ?>
            
         <?php else: ?>
            <div class="empty-state animated-card">
               <i class="fas fa-inbox"></i>
               <h4>No Messages Found</h4>
               <p class="text-muted">
                  <?php 
                  if(!empty($search)) {
                     echo 'There are no messages matching your search.';
                  } elseif($filter === 'read') {
                     echo 'There are no read messages.';
                  } elseif($filter === 'unread') {
                     echo 'There are no unread messages.';
                  } else {
                     echo 'There are no messages at this time.';
                  }
                  ?>
               </p>
               <?php if(!empty($search) || $filter !== 'all'): ?>
                  <a href="admin_contacts.php" class="btn btn-outline-primary mt-3">View All Messages</a>
               <?php endif; ?>
            </div>
         <?php endif; ?>
      </div>
   </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="custom-modal">
   <div class="modal-content">
      <div class="modal-header">
         <h4>Confirm Deletion</h4>
         <button type="button" class="btn-close close-modal"></button>
      </div>
      <p>Are you sure you want to delete this message? This action cannot be undone.</p>
      <div class="modal-actions">
         <button class="btn btn-secondary close-modal">Cancel</button>
         <a id="confirmDelete" href="#" class="btn btn-danger">Delete</a>
      </div>
   </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="custom-modal">
   <div class="modal-content">
      <div class="modal-header">
         <h4>Reply to Message</h4>
         <button type="button" class="btn-close close-modal"></button>
      </div>
      <form id="replyForm">
         <div class="mb-3">
            <label for="replyName" class="form-label">To:</label>
            <div class="input-group">
               <span class="input-group-text"><i class="fas fa-user"></i></span>
               <input type="text" class="form-control" id="replyName" readonly>
            </div>
         </div>
         <div class="mb-3">
            <label for="replyEmail" class="form-label">Email:</label>
            <div class="input-group">
               <span class="input-group-text"><i class="fas fa-envelope"></i></span>
               <input type="email" class="form-control" id="replyEmail" readonly>
            </div>
         </div>
         <div class="mb-3">
            <label for="replySubject" class="form-label">Subject:</label>
            <div class="input-group">
               <span class="input-group-text"><i class="fas fa-heading"></i></span>
               <input type="text" class="form-control" id="replySubject" required>
            </div>
         </div>
         <div class="mb-3">
            <label for="replyMessage" class="form-label">Message:</label>
            <textarea class="form-control" id="replyMessage" rows="5" required></textarea>
         </div>
         <div class="modal-actions">
            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-with-icon">
               <i class="fas fa-paper-plane"></i> Send Reply
            </button>
         </div>
      </form>
   </div>
</div>

<!-- Theme toggle button -->
<div class="theme-toggle" id="themeToggle">
   <i class="fas fa-moon"></i>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom admin JS file link -->
<script src="js/admin_script.js"></script>

<script>
   // Delete confirmation modal
   const deleteModal = document.getElementById('deleteModal');
   const replyModal = document.getElementById('replyModal');
   const deleteButtons = document.querySelectorAll('.delete-btn');
   const replyButtons = document.querySelectorAll('.reply-btn');
   const closeModalButtons = document.querySelectorAll('.close-modal');
   const confirmDelete = document.getElementById('confirmDelete');
   const loader = document.querySelector('.loader');
   const themeToggle = document.getElementById('themeToggle');
   
   // Check for saved theme preference
   if (localStorage.getItem('darkMode') === 'enabled') {
      document.body.classList.add('dark-mode');
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
   }
   
   // Theme toggle functionality
   themeToggle.addEventListener('click', function() {
      document.body.classList.toggle('dark-mode');
      if (document.body.classList.contains('dark-mode')) {
         localStorage.setItem('darkMode', 'enabled');
         themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
         localStorage.setItem('darkMode', 'disabled');
         themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }
   });
   
   // Open delete modal
   deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
         const messageId = this.getAttribute('data-id');
         confirmDelete.href = `admin_contacts.php?delete=${messageId}`;
         deleteModal.style.display = 'block';
      });
   });
   
   // Open reply modal
   replyButtons.forEach(button => {
      button.addEventListener('click', function() {
         const email = this.getAttribute('data-email');
         const name = this.getAttribute('data-name');
         document.getElementById('replyEmail').value = email;
         document.getElementById('replyName').value = name;
         document.getElementById('replySubject').value = `RE: Your message to BookCraft`;
         replyModal.style.display = 'block';
      });
   });
   
   // Close modals
   closeModalButtons.forEach(button => {
      button.addEventListener('click', function() {
         deleteModal.style.display = 'none';
         replyModal.style.display = 'none';
      });
   });
   
   // Close modals when clicking outside
   window.addEventListener('click', function(event) {
      if (event.target === deleteModal) {
         deleteModal.style.display = 'none';
      }
      if (event.target === replyModal) {
         replyModal.style.display = 'none';
      }
   });
   
   // Show loading spinner when navigating
   document.addEventListener('click', function(e) {
      const target = e.target;
      if (target.tagName === 'A' && !target.classList.contains('close-modal') && target.getAttribute('href') !== '#') {
         loader.style.display = 'flex';
      }
   });
   
   // Show loading on form submission
   document.getElementById('searchForm').addEventListener('submit', function() {
      loader.style.display = 'flex';
   });
   
   // Handle reply form submission
   document.getElementById('replyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // In a real implementation, you would send the email via AJAX
      loader.style.display = 'flex';
      
      setTimeout(function() {
         loader.style.display = 'none';
         replyModal.style.display = 'none';
         
         // Show success message
         const alertDiv = document.createElement('div');
         alertDiv.className = 'alert alert-success alert-dismissible fade show';
         alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i> Your reply has been sent successfully to ${document.getElementById('replyEmail').value}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         `;
         
         document.querySelector('.page-header').insertAdjacentElement('afterend', alertDiv);
         
         // Auto dismiss after 5 seconds
         setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 500);
         }, 5000);
         
         // Reset form
         document.getElementById('replyForm').reset();
      }, 1500);
   });
   
   // Hide loading spinner when page is fully loaded
   window.addEventListener('load', function() {
      loader.style.display = 'none';
   });
   
   // Add staggered animation effect to message cards
   document.addEventListener('DOMContentLoaded', function() {
      const messageCards = document.querySelectorAll('.message-card');
      messageCards.forEach((card, index) => {
         setTimeout(() => {
            card.style.opacity = '1';
         }, 100 * index);
      });
   });
</script>

</body>
</html>