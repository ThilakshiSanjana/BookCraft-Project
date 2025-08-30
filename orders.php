<?php


include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if(!isset($user_id)){
   header('location:login.php');
   exit();
}


$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;


$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'placed_on';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';


$valid_sort_fields = ['placed_on', 'total_price', 'payment_status'];
$sort_by = in_array($sort_by, $valid_sort_fields) ? $sort_by : 'placed_on';


$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY $sort_by $sort_order LIMIT ?, ?");
$stmt->bind_param("iii", $user_id, $start_from, $results_per_page);
$stmt->execute();
$order_query = $stmt->get_result();


$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Orders | BookCraft</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">

   <!-- Leaflet CSS for maps -->
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      :root {
         --primary-color: #6c5ce7;
         --secondary-color:rgb(168, 137, 234);
         --accent-color:rgba(2, 75, 16, 0.97);
         --success-color:#00b872;
         --warning-color: #fdcb6e;
         --danger-color: #d63031;
         --bg-color: #f7f9fc;
         --text-dark:rgb(47, 45, 54);
         --text-light: #636e72;
         --text-white: #ffffff;
         --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
         --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
         --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
         --radius-sm: 8px;
         --radius-md: 12px;
         --radius-lg: 20px;
         --transition: all 0.3s ease;
         --font-family: 'Poppins', sans-serif;
      }

      body {
         background-color: var(--bg-color);
         font-family: var(--font-family);
         color: var(--text-dark);
      }

      .orders-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 20px;
      }

      .page-header {
         display: flex;
         flex-direction: column;
         align-items: center;
         margin-bottom: 40px;
         text-align: center;
      }

      .page-title {
         color: var(--text-dark);
         font-size: 2.5rem;
         font-weight: 700;
         margin-bottom: 10px;
         letter-spacing: -0.5px;
      }

      .page-subtitle {
         color: var(--text-dark);
         font-size: 1.8rem;
         max-width: 600px;
      }

      /* Filter and Control Panel */
      .control-panel {
         display: flex;
         flex-direction: column;
         background-color: var(--text-white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         padding: 20px;
         margin-bottom: 30px;
      }

      .panel-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 20px;
      }

      .panel-title {
         font-size: 1.9rem;
         font-weight: 600;
         color: var(--text-dark);
         display: flex;
         align-items: center;
         gap: 10px;
      }

      .panel-title i {
         color: var(--primary-color);
      }

      .filter-form {
         display: flex;
         flex-wrap: wrap;
         gap: 15px;
      }

      .form-group {
         flex: 1;
         min-width: 200px;
      }

      .form-group label {
         display: block;
         margin-bottom: 8px;
         font-size: 1.5rem;
         color: var(--text-darkt);
         font-weight: 550;
      }

      .form-select {
         width: 100%;
         padding: 12px 15px;
         border: 1px solid #e0e0e0;
         border-radius: var(--radius-sm);
         background-color: var(--text-white);
         font-size: 1.5rem;
         font-weight: 410;
         color: var(--text-dark);
         cursor: pointer;
         transition: var(--transition);
         appearance: none;
         background-image: url("data:image/jpeg;base64,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");
         background-repeat: no-repeat;
         background-position: right 15px center;
         padding-right: 35px;
         
      }

      .form-select:hover {
         border-color: var(--primary-color);
      }

      .form-select:focus {
         outline: none;
         border-color: var(--primary-color);
         box-shadow: 0 0 0 3px rgba(243, 12, 224, 0.1);
      }

      /* Orders List View */
      .orders-list {
         display: flex;
         flex-direction: column;
         gap: 20px;
         margin-bottom: 40px;
      }

      .order-item {
         position: relative;
         background-color: var(--text-black);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         overflow: hidden;
         transition: var(--transition);
         display: grid;
         grid-template-columns: 1fr 2fr 1fr;
         align-items: center;
      }

      .order-item:hover {
         transform: translateY(-5px);
         box-shadow: var(--shadow-lg);
      }

      .order-status {
         display: flex;
         flex-direction: column;
         align-items: center;
         padding: 20px;
         border-right: 1px solid rgba(0,0,0,0.05);
      }

      .status-indicator {
         width: 70px;
         height: 70px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-bottom: 10px;
      }

      .status-pending {
         background-color: rgba(243, 165, 21, 0.95);
         color: var(--warning-color);
      }

      .status-completed {
         background-color: rgba(14, 81, 8, 0.94);
         color: var(--success-color);
      }

      .status-icon {
         font-size: 3rem;
      }

      .status-text {
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark-color);
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .order-details {
         padding: 20px;
      }

      .order-date {
         color: var(--text-dark);
         font-size: 1.5rem;
         margin-bottom: 5px;
      }

      .order-id {
         font-size: 1.5rem;
         font-weight: 600;
         margin-bottom: 15px;
         color: var(--primary-color);
      }

      .detail-grid {
         display: grid;
         grid-template-columns: repeat(2, 1fr);
         gap: 15px;
      }

      .detail-item {
         display: flex;
         flex-direction: column;
      }

      .detail-label {
         font-size: 1.5rem;
         font-weight: 650;
         color: var(--danger-color);
         margin-bottom: 5px;
         
      }

      .detail-value {
          font-size: 1.3rem;
         font-weight: 410;
         color: var(--dark-color);
      }

      .order-actions {
         display: flex;
         flex-direction: column;
         gap: 10px;
         align-items: center;
         padding: 20px;
         border-left: 1px solid rgba(0,0,0,0.05);
      }

      .order-price {
         font-size: 1.7rem;
         font-weight: 700;
         color: var(--accent-color);
         margin-bottom: 10px;
      }

      .item-count {
         display: flex;
         align-items: center;
         gap: 5px;
         color: var( --success-color);
         font-size: 1.6rem;
         font-weight: 500;
         margin-bottom: 20px;
      }

      .action-btn {
         font-size: 1.5rem;
         font-weight: 700;
         width: 100%;
         padding: 12px 20px;
         border-radius: var(--radius-sm);
         text-align: center;
         transition: var(--transition);
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
         text-decoration: none;
      }

      .btn-primary {
         background-color: var(--primary-color);
         color: var(--text-white);
      }

      .btn-primary:hover {
         background-color:rgb(12, 143, 251);
      }

      .btn-outline {
         background-color: transparent;
         border: 1px solid var(--text-light);
         color: var(--text-dark);
      }

      .btn-outline:hover {
         border-color: var(--primary-color);
         color: var(--primary-color);
      }

      .btn-track {
         background-color: var(--secondary-color);
         color: var(--text-white);
      }

      .btn-track:hover {
         background-color:rgb(154, 108, 233);
      }

      /* Pagination */
      .pagination-wrapper {
         display: flex;
         justify-content: center;
         margin: 40px 0;
      }

      .pagination {
         display: flex;
         align-items: center;
         background-color: var(--text-white);
         border-radius: 50px;
         box-shadow: var(--shadow-md);
         padding: 5px;
      }

      .page-item {
         display: inline-flex;
         align-items: center;
         justify-content: center;
         min-width: 40px;
         height: 40px;
         margin: 0 2px;
         border-radius: 50%;
         font-weight: 500;
         text-decoration: none;
         color: var(--text-dark);
         transition: var(--transition);
      }

      .page-item:hover {
         background-color: rgba(108, 92, 231, 0.1);
      }

      .page-item.active {
         background-color: var(--primary-color);
         color: var(--text-white);
      }

      .page-item.disabled {
         opacity: 0.5;
         cursor: not-allowed;
      }

      .ellipsis {
         font-weight: bold;
         letter-spacing: 2px;
      }

      /* Empty State */
      .empty-container {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         text-align: center;
         padding: 60px 20px;
         background-color: var(--text-white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
      }

      .empty-illustration {
         max-width: 250px;
         margin-bottom: 30px;
      }

      .empty-title {
         font-size: 1.8rem;
         font-weight: 700;
         color: var(--text-dark);
         margin-bottom: 15px;
      }

      .empty-message {
         font-size: 1.1rem;
         color: var(--text-light);
         max-width: 500px;
         margin: 0 auto 30px;
      }

      .shop-now-btn {
         padding: 15px 30px;
         background-color: var(--primary-color);
         color: var(--text-white);
         border-radius: var(--radius-sm);
         font-weight: 600;
         text-decoration: none;
         transition: var(--transition);
         display: inline-flex;
         align-items: center;
         gap: 10px;
      }

      .shop-now-btn:hover {
         background-color: #5849e3;
         transform: translateY(-3px);
      }

      /* Tracking Modal */
      .tracking-modal {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
         z-index: 1000;
         justify-content: center;
         align-items: center;
      }

      .tracking-modal.active {
         display: flex;
      }

      .modal-content {
         background-color: var(--text-white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-lg);
         width: 90%;
         max-width: 900px;
         max-height: 90vh;
         overflow: hidden;
         display: flex;
         flex-direction: column;
         animation: modalFadeIn 0.3s ease;
      }

      @keyframes modalFadeIn {
         from {
            opacity: 0;
            transform: translateY(-30px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }

      .modal-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 20px 25px;
         border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      }

      .modal-title {
         font-size: 1.5rem;
         font-weight: 700;
         color: var(--primary-color);
         display: flex;
         align-items: center;
         gap: 10px;
      }

      .modal-close {
         background: none;
         border: none;
         font-size: 1.5rem;
         color: var(--text-light);
         cursor: pointer;
         transition: var(--transition);
      }

      .modal-close:hover {
         color: var(--danger-color);
      }

      .modal-body {
         padding: 20px 25px;
         overflow-y: auto;
      }

      .tracking-container {
         display: flex;
         flex-direction: column;
         gap: 20px;
      }

      .tracking-map-container {
         height: 400px;
         border-radius: var(--radius-sm);
         overflow: hidden;
         border: 1px solid rgba(0, 0, 0, 0.1);
      }

      #tracking-map {
         height: 100%;
         width: 100%;
      }

      .tracking-info {
         display: flex;
         gap: 20px;
      }

      .tracking-stages {
         flex: 1;
      }

      .tracking-title {
         font-size: 1.1rem;
         font-weight: 600;
         margin-bottom: 15px;
         color: var(--text-dark);
      }

      .tracking-timeline {
         position: relative;
         padding-left: 30px;
      }

      .tracking-timeline::before {
         content: '';
         position: absolute;
         top: 0;
         left: 10px;
         height: 100%;
         width: 2px;
         background-color: #e0e0e0;
      }

      .timeline-item {
         position: relative;
         padding-bottom: 25px;
      }

      .timeline-item:last-child {
         padding-bottom: 0;
      }

      .timeline-item::before {
         content: '';
         position: absolute;
         left: -30px;
         top: 0;
         width: 20px;
         height: 20px;
         border-radius: 50%;
         background-color: var(--text-white);
         border: 2px solid #e0e0e0;
         z-index: 1;
      }

      .timeline-item.active::before {
         background-color: var(--primary-color);
         border-color: var(--primary-color);
      }

      .timeline-item.completed::before {
         background-color: var(--success-color);
         border-color: var(--success-color);
      }

      .timeline-content {
         padding-left: 10px;
      }

      .timeline-date {
         font-size: 1.2rem;
         color: var(--text-danger);
         margin-bottom: 5px;
      }

      .timeline-title {
         font-weight: 600;
         margin-bottom: 5px;
         color: var(--text-danger);
      }

      .timeline-description {
         font-size: 1.5rem;
         color: var(--text-dark);
      }

      .tracking-details {
         flex: 1;
         background-color: rgba(108, 92, 231, 0.05);
         padding: 20px;
         border-radius: var(--radius-sm);
      }

      .tracking-detail {
         margin-bottom: 15px;
      }

      .tracking-detail:last-child {
         margin-bottom: 0;
      }

      .detail-heading {
         font-size: 1.1rem;
         color: var(--text-danger);
         margin-bottom: 5px;
      }

      .detail-content {
         font-size: 1.1rem;
         font-weight: 500;
         color: var(--text-dark);
      }

      .modal-footer {
         padding: 15px 25px;
         border-top: 1px solid rgba(0, 0, 0, 0.05);
         display: flex;
         justify-content: flex-end;
         gap: 10px;
      }

      .modal-btn {
         padding: 10px 20px;
         border-radius: var(--radius-sm);
         font-weight: 500;
         cursor: pointer;
         transition: var(--transition);
      }

      .modal-btn.primary {
         background-color: var(--primary-color);
         color: var(--text-white);
         border: none;
      }

      .modal-btn.primary:hover {
         background-color: #5849e3;
      }

      .modal-btn.secondary {
         background-color: transparent;
         border: 1px solid #e0e0e0;
         color: var(--text-dark);
      }

      .modal-btn.secondary:hover {
         border-color: var(--primary-color);
         color: var(--primary-color);
      }

      /* Responsive Design */
      @media (max-width: 992px) {
         .order-item {
            grid-template-columns: 80px 1fr;
         }

         .order-status {
            grid-row: span 2;
            padding: 15px;
            border-right: none;
            border-bottom: none;
         }

         .status-indicator {
            width: 50px;
            height: 50px;
         }

         .status-icon {
            font-size: 1.5rem;
         }

         .order-details {
            grid-column: 2;
            padding: 15px 15px 5px;
         }

         .order-actions {
            grid-column: 2;
            grid-row: 2;
            flex-direction: row;
            padding: 5px 15px 15px;
            border-left: none;
            border-top: 1px solid rgba(0,0,0,0.05);
            justify-content: space-between;
         }

         .order-price {
            margin-bottom: 0;
         }

         .action-btn {
            width: auto;
         }

         .tracking-info {
            flex-direction: column;
         }
      }

      @media (max-width: 768px) {
         .panel-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
         }

         .form-group {
            width: 100%;
         }

         .detail-grid {
            grid-template-columns: 1fr;
         }

         .order-actions {
            flex-direction: column;
            align-items: flex-start;
         }

         .action-btn {
            width: 100%;
         }

         .tracking-map-container {
            height: 300px;
         }
      }

      @media (max-width: 576px) {
         .page-title {
            font-size: 2rem;
         }

         .order-item {
            grid-template-columns: 1fr;
         }

         .order-status {
            flex-direction: row;
            justify-content: flex-start;
            gap: 15px;
            grid-row: 1;
            padding: 15px;
         }

         .status-indicator {
            margin-bottom: 0;
            width: 40px;
            height: 40px;
         }

         .status-icon {
            font-size: 1.2rem;
         }

         .order-details {
            grid-column: 1;
            grid-row: 2;
         }

         .order-actions {
            grid-column: 1;
            grid-row: 3;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Your Orders</h3>
   <p><a href="home.php">Home</a> / Orders</p>
</div>

<section class="placed-orders">
   <div class="orders-container">
      <div class="page-header">
         <h1 class="page-title">Your Order History</h1>
         <p class="page-subtitle">Track all your book purchases and manage your orders in one place</p>
      </div>

      <!-- Control Panel -->
      <div class="control-panel">
         <div class="panel-header">
            <div class="panel-title">
               <i class="fas fa-sliders-h"></i> Sort & Filter Options
            </div>
         </div>
         
         <form class="filter-form" action="" method="GET">
            <div class="form-group">
               <label for="sort-by">Sort By</label>
               <select id="sort-by" class="form-select" name="sort" onchange="this.form.submit()">
                  <option value="placed_on" <?php if($sort_by == 'placed_on') echo 'selected'; ?>>Date Ordered</option>
                  <option value="total_price" <?php if($sort_by == 'total_price') echo 'selected'; ?>>Price</option>
                  <option value="payment_status" <?php if($sort_by == 'payment_status') echo 'selected'; ?>>Payment Status</option>
               </select>
            </div>
            
            <div class="form-group">
               <label for="sort-order">Order</label>
               <select id="sort-order" class="form-select" name="order" onchange="this.form.submit()">
                  <option value="DESC" <?php if($sort_order == 'DESC') echo 'selected'; ?>>Newest to Oldest</option>
                  <option value="ASC" <?php if($sort_order == 'ASC') echo 'selected'; ?>>Oldest to Newest</option>
               </select>
            </div>
            
            <input type="hidden" name="page" value="<?php echo $page; ?>">
         </form>
      </div>

      <?php if(mysqli_num_rows($order_query) > 0): ?>
         <div class="orders-list">
            <?php 
            while($fetch_orders = mysqli_fetch_assoc($order_query)){
               // Format the date
               $formatted_date = date('F j, Y, g:i a', strtotime($fetch_orders['placed_on']));
               $order_day = date('d', strtotime($fetch_orders['placed_on']));
               $order_month = date('M', strtotime($fetch_orders['placed_on']));
               $order_year = date('Y', strtotime($fetch_orders['placed_on']));
               
               // Count number of items from total_products
               $products_count = substr_count($fetch_orders['total_products'], ',') + 1;
               
               // Status icon based on payment status
               $status_icon = $fetch_orders['payment_status'] == 'completed' ? 'fa-check-circle' : 'fa-clock';
            ?>
            <div class="order-item">
               <div class="order-status">
                  <div class="status-indicator status-<?php echo $fetch_orders['payment_status']; ?>">
                     <i class="fas <?php echo $status_icon; ?> status-icon"></i>
                  </div>
                  <span class="status-text"><?php echo ucfirst(htmlspecialchars($fetch_orders['payment_status'])); ?></span>
               </div>
               
               <div class="order-details">
                  <div class="order-date">
                     <i class="fas fa-calendar-alt"></i> <?php echo $formatted_date; ?>
                  </div>
                  <div class="order-id">Order #<?php echo htmlspecialchars($fetch_orders['id']); ?></div>
                  
                  <div class="detail-grid">
                     <div class="detail-item">
                        <span class="detail-label">Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($fetch_orders['name']); ?></span>
                     </div>
                     
                     <div class="detail-item">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value"><?php echo htmlspecialchars($fetch_orders['method']); ?></span>
                     </div>
                     
                     <div class="detail-item">
                        <span class="detail-label">Address</span>
                        <span class="detail-value"><?php echo htmlspecialchars($fetch_orders['address']); ?></span>
                     </div>
                     
                     <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($fetch_orders['email'] ?? 'N/A'); ?></span>
                     </div>
                  </div>
               </div>
               
               <div class="order-actions">
                  <div class="order-price">Rs<?php echo htmlspecialchars($fetch_orders['total_price']); ?></div>
                  <div class="item-count">
                     <i class="fas fa-book"></i> <?php echo $products_count; ?> item<?php echo $products_count > 1 ? 's' : ''; ?>
                  </div>
                  <a href="order_details.php?id=<?php echo $fetch_orders['id']; ?>" class="action-btn btn-primary">
                     
                  </a>
                  <button class="action-btn btn-track" onclick="openTrackingModal(<?php echo $fetch_orders['id']; ?>)">
                     <i class="fas fa-map-marker-alt"></i> Track Order
                  </button>
                  <a href="#" class="action-btn btn-outline" onclick="window.print();">
                     <i class="fas fa-print"></i> Print Receipt
                  </a>
               </div>
            </div>
            <?php } ?>
         </div>
         
         <!-- Pagination -->
         <?php if($total_pages > 1): ?>
         <div class="pagination-wrapper">
            <div class="pagination">
               <?php if($page > 1): ?>
                  <a href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="page-item">
                     <i class="fas fa-chevron-left"></i>
                  </a>
               <?php else: ?>
                  <span class="page-item disabled"><i class="fas fa-chevron-left"></i></span>
               <?php endif; ?>
               
               <?php
               // Show limited page numbers with ellipsis
               $start_page = max(1, $page - 2);
               $end_page = min($total_pages, $page + 2);
               
               if ($start_page > 1) {
                  echo '<a href="?page=1&sort='.$sort_by.'&order='.$sort_order.'" class="page-item">1</a>';
                  if ($start_page > 2) {
                     echo '<span class="page-item ellipsis">...</span>';
                  }
               }
               
               for($i = $start_page; $i <= $end_page; $i++): ?>
                  <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" 
                     class="page-item <?php if($i == $page) echo 'active'; ?>">
                     <?php echo $i; ?>
                  </a>
               <?php endfor; 
               
               if ($end_page < $total_pages) {
                  if ($end_page < $total_pages - 1) {
                     echo '<span class="page-item ellipsis">...</span>';
                  }
                  echo '<a href="?page='.$total_pages.'&sort='.$sort_by.'&order='.$sort_order.'" class="page-item">'.$total_pages.'</a>';
               }
               ?>
               
               <?php if($page < $total_pages): ?>
                  <a href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="page-item">
                     <i class="fas fa-chevron-right"></i>
                  </a>
               <?php else: ?>
                  <span class="page-item disabled"><i class="fas fa-chevron-right"></i></span>
               <?php endif; ?>
            </div>
         </div>
         <?php endif; ?>
         
      <?php else: ?>
         <!-- Empty State -->
         <div class="empty-container">
            <div class="empty-illustration">
               <i class="fas fa-shopping-bag fa-6x" style="color: #e0e0e0;"></i>
            </div>
            <h2 class="empty-title">You haven't placed any orders yet</h2>
            <p class="empty-message">
               Discover our amazing collection of books and start building your personal library today!
            </p>
            <a href="shop.php" class="shop-now-btn">
               <i class="fas fa-book"></i> Browse Catalog
            </a>
         </div>
      <?php endif; ?>
   </div>
</section>

<!-- Tracking Modal -->
<div id="tracking-modal" class="tracking-modal">
   <div class="modal-content">
      <div class="modal-header">
         <div class="modal-title">
            <i class="fas fa-truck"></i> Order Tracking
         </div>
         <button class="modal-close" onclick="closeTrackingModal()">
            <i class="fas fa-times"></i>
         </button>
      </div>
      <div class="modal-body">
         <div class="tracking-container">
            <div class="tracking-map-container">
               <div id="tracking-map"></div>
            </div>
            
            <div class="tracking-info">
               <div class="tracking-stages">
                  <h3 class="tracking-title">Delivery Progress</h3>
                  <div class="tracking-timeline" id="tracking-timeline">
                     <!-- Timeline items will be populated by JavaScript -->
                  </div>
               </div>
               
               <div class="tracking-details">
                  <h3 class="tracking-title">Shipment Details</h3>
                  <div class="tracking-detail">
                     <div class="detail-heading">Order ID</div>
                     <div class="detail-content" id="tracking-order-id">-</div>
                  </div>
                  <div class="tracking-detail">
                     <div class="detail-heading">Estimated Delivery</div>
                     <div class="detail-content" id="tracking-estimated-delivery">-</div>
                  </div>
                  <div class="tracking-detail">
                     <div class="detail-heading">Shipping Method</div>
                     <div class="detail-content" id="tracking-shipping-method">Express Delivery</div>
                  </div>
                  <div class="tracking-detail">
                     <div class="detail-heading">Current Location</div>
                     <div class="detail-content" id="tracking-current-location">-</div>
                  </div>
                  <div class="tracking-detail">
                     <div class="detail-heading">Delivery Address</div>
                     <div class="detail-content" id="tracking-delivery-address">-</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="modal-footer">
         <button class="modal-btn secondary" onclick="closeTrackingModal()">Close</button>
         <button class="modal-btn primary" onclick="shareTracking()">
            <i class="fas fa-share-alt"></i> Share Tracking
         </button>
      </div>
   </div>
</div>

<?php include 'footer.php'; ?>

<!-- Leaflet JS for maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Animate orders on page load
      const orderItems = document.querySelectorAll('.order-item');
      orderItems.forEach((item, index) => {
         item.style.opacity = '0';
         item.style.transform = 'translateY(20px)';
         setTimeout(() => {
            item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
         }, 100 * index);
      });
      
      // Hover effect for order items
      orderItems.forEach(item => {
         item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = 'var(--shadow-lg)';
         });
         
         item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow-md)';
         });
      });
   });
   
   // Map and tracking variables
   let map;
   let trackingMarker;
   let trackingPath;
   let currentOrderId;
   
   // Sri Lanka tracking locations
   const sriLankaLocations = {
      'colombo': { lat: 6.9271, lng: 79.8612, name: 'Colombo' },
      'kandy': { lat: 7.2906, lng: 80.6337, name: 'Kandy' },
      'matale': { lat: 6.0535, lng: 80.2210, name: 'Matale' },
      'kurunegala': { lat: 9.6615, lng: 80.0255, name: 'Kurunegala' },
      'yatawatta': { lat: 8.5874, lng: 81.2152, name: 'Yatawatta' },
      'rideegama': { lat: 7.7170, lng: 81.7000, name: 'Rideegama' },
      'negombo': { lat: 7.2083, lng: 79.8358, name: 'Negombo' },
      'matara': { lat: 5.9485, lng: 80.5353, name: 'Matara' },
      'anuradhapura': { lat: 8.3114, lng: 80.4037, name: 'Anuradhapura' },
      'ratnapura': { lat: 6.7056, lng: 80.3847, name: 'Ratnapura' }
   };
   
   // Sri Lanka center coordinates
   const sriLankaCenter = { lat: 7.8731, lng: 80.7718 };
   
   // Initialize map
   function initMap() {
      // Create map centered on Sri Lanka
      map = L.map('tracking-map').setView([sriLankaCenter.lat, sriLankaCenter.lng], 8);
      
      // Add OpenStreetMap tile layer
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
         attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);
   }
   
   // Open tracking modal
   function openTrackingModal(orderId) {
      currentOrderId = orderId;
      document.getElementById('tracking-modal').classList.add('active');
      document.body.style.overflow = 'hidden';
      
      // Initialize map if not already
      if (!map) {
         setTimeout(() => {
            initMap();
            loadOrderTracking(orderId);
         }, 100);
      } else {
         // If map exists, just update for the new order
         loadOrderTracking(orderId);
      }
   }
   
   // Close tracking modal
   function closeTrackingModal() {
      document.getElementById('tracking-modal').classList.remove('active');
      document.body.style.overflow = 'auto';
   }
   
   // Share tracking info
   function shareTracking() {
      if (navigator.share) {
         navigator.share({
            title: 'My Order Tracking',
            text: `Track my BookCraft order #${currentOrderId}`,
            url: window.location.href
         })
         .catch(error => console.log('Error sharing:', error));
      } else {
         alert('Sharing is not supported on this browser. Copy the URL to share.');
      }
   }
   
   // Load order tracking data
   function loadOrderTracking(orderId) {
      // Update order ID in the UI
      document.getElementById('tracking-order-id').textContent = `#${orderId}`;
      
      // Get delivery address from the order
      const orderElement = document.querySelector(`.order-item:has(a[href="order_details.php?id=${orderId}"])`);
      let deliveryAddress = "Sri Lanka";
      
      if (orderElement) {
         const addressElement = orderElement.querySelector('.detail-value');
         if (addressElement) {
            deliveryAddress = addressElement.textContent;
         }
      }
      
      document.getElementById('tracking-delivery-address').textContent = deliveryAddress;
      
      // Generate tracking data based on order ID
      // This is simulated data - in a real app this would come from an API
      const trackingData = generateTrackingData(orderId);
      
      // Update estimated delivery
      document.getElementById('tracking-estimated-delivery').textContent = trackingData.estimatedDelivery;
      
      // Update current location
      document.getElementById('tracking-current-location').textContent = trackingData.currentLocation.name;
      
      // Update timeline
      updateTrackingTimeline(trackingData.stages);
      
      // Update map
      updateTrackingMap(trackingData);
   }
   
   // Generate tracking data (simulated)
   function generateTrackingData(orderId) {
      // Use order ID to seed the random generation for consistent results
      const seed = parseInt(orderId);
      
      // Helper function to get pseudo-random number based on seed
      const getRandom = (max, offset = 0) => {
         return ((seed * 9301 + 49297) % 233280) / 233280 * max + offset;
      };
      
      // Get random locations from Sri Lanka
      const locationKeys = Object.keys(sriLankaLocations);
      const startLocationKey = locationKeys[Math.floor(getRandom(3))]; // One of the first 3 major cities
      const currentLocationIndex = Math.min(Math.floor(getRandom(locationKeys.length)), locationKeys.length - 1);
      const currentLocationKey = locationKeys[currentLocationIndex];
      const destinationIndex = Math.floor(getRandom(locationKeys.length - currentLocationIndex - 1) + currentLocationIndex + 1);
      const destinationLocationKey = locationKeys[Math.min(destinationIndex, locationKeys.length - 1)];
      
      // Generate dates
      const orderDate = new Date();
      orderDate.setDate(orderDate.getDate() - Math.floor(getRandom(5, 1))); // 1-5 days ago
      
      const processingDate = new Date(orderDate);
      processingDate.setHours(processingDate.getHours() + Math.floor(getRandom(8, 2))); // 2-10 hours after order
      
      const dispatchDate = new Date(processingDate);
      dispatchDate.setHours(dispatchDate.getHours() + Math.floor(getRandom(10, 4))); // 4-14 hours after processing
      
      const inTransitDate = new Date(dispatchDate);
      inTransitDate.setHours(inTransitDate.getHours() + Math.floor(getRandom(12, 6))); // 6-18 hours after dispatch
      
      const estimatedDelivery = new Date(orderDate);
      estimatedDelivery.setDate(estimatedDelivery.getDate() + Math.floor(getRandom(4, 3))); // 3-7 days after order
      
      // Create route coordinates
      const routeCoordinates = [
         [sriLankaLocations[startLocationKey].lat, sriLankaLocations[startLocationKey].lng],
         [sriLankaLocations[currentLocationKey].lat, sriLankaLocations[currentLocationKey].lng]
      ];
      
      if (getRandom(10) > 5) {
         // Add intermediate point for more complex routes
         const intermediateKey = locationKeys[Math.floor(getRandom(locationKeys.length))];
         routeCoordinates.splice(1, 0, [sriLankaLocations[intermediateKey].lat, sriLankaLocations[intermediateKey].lng]);
      }
      
      // Add destination
      routeCoordinates.push([sriLankaLocations[destinationLocationKey].lat, sriLankaLocations[destinationLocationKey].lng]);
      
      // Determine current stage based on dates
      const now = new Date();
      let currentStage = 'order_placed';
      
      if (now > inTransitDate) {
         currentStage = 'in_transit';
      } else if (now > dispatchDate) {
         currentStage = 'dispatched';
      } else if (now > processingDate) {
         currentStage = 'processing';
      }
      
      // Generate stages data
      const stages = [
         {
            id: 'order_placed',
            title: 'Order Placed',
            date: formatDate(orderDate),
            description: `Your order #${orderId} has been received and confirmed.`,
            status: 'completed'
         },
         {
            id: 'processing',
            title: 'Processing',
            date: formatDate(processingDate),
            description: 'Your order is being processed and packed at our warehouse.',
            status: currentStage === 'processing' ? 'active' : (now > processingDate ? 'completed' : 'pending')
         },
         {
            id: 'dispatched',
            title: 'Dispatched',
            date: formatDate(dispatchDate),
            description: `Your order has been dispatched from ${sriLankaLocations[startLocationKey].name}.`,
            status: currentStage === 'dispatched' ? 'active' : (now > dispatchDate ? 'completed' : 'pending')
         },
         {
            id: 'in_transit',
            title: 'In Transit',
            date: formatDate(inTransitDate),
            description: `Your package is in transit from ${sriLankaLocations[currentLocationKey].name} to your location.`,
            status: currentStage === 'in_transit' ? 'active' : (now > inTransitDate ? 'completed' : 'pending')
         },
         {
            id: 'delivered',
            title: 'Delivered',
            date: formatDate(estimatedDelivery),
            description: 'Your order will be delivered to your address.',
            status: 'pending'
         }
      ];
      
      return {
         orderId: orderId,
         estimatedDelivery: formatDate(estimatedDelivery),
         startLocation: sriLankaLocations[startLocationKey],
         currentLocation: sriLankaLocations[currentLocationKey],
         destinationLocation: sriLankaLocations[destinationLocationKey],
         routeCoordinates: routeCoordinates,
         stages: stages,
         currentStage: currentStage
      };
   }
   
   // Update tracking timeline in the UI
   function updateTrackingTimeline(stages) {
      const timelineContainer = document.getElementById('tracking-timeline');
      timelineContainer.innerHTML = '';
      
      stages.forEach(stage => {
         const timelineItem = document.createElement('div');
         timelineItem.className = `timeline-item ${stage.status}`;
         
         timelineItem.innerHTML = `
            <div class="timeline-content">
               <div class="timeline-date">${stage.date}</div>
               <div class="timeline-title">${stage.title}</div>
               <div class="timeline-description">${stage.description}</div>
            </div>
         `;
         
         timelineContainer.appendChild(timelineItem);
      });
   }
   
   // Update tracking map
   function updateTrackingMap(trackingData) {
      // Clear existing markers and paths
      if (trackingMarker) {
         map.removeLayer(trackingMarker);
      }
      
      if (trackingPath) {
         map.removeLayer(trackingPath);
      }
      
      // Center map on the route
      const bounds = L.latLngBounds(trackingData.routeCoordinates);
      map.fitBounds(bounds, { padding: [50, 50] });
      
      // Create path between coordinates
      trackingPath = L.polyline(trackingData.routeCoordinates, {
         color: '#6c5ce7',
         weight: 4,
         opacity: 0.7,
         dashArray: '10, 10',
         lineCap: 'round'
      }).addTo(map);
      
      // Add start marker
      L.marker([trackingData.startLocation.lat, trackingData.startLocation.lng], {
         icon: L.divIcon({
            className: 'custom-map-marker',
            html: '<i class="fas fa-warehouse" style="color: #6c5ce7; font-size: 20px;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
         })
      }).addTo(map).bindPopup(`<b>Starting Point</b><br>${trackingData.startLocation.name}`);
      
      // Add destination marker
      L.marker([trackingData.destinationLocation.lat, trackingData.destinationLocation.lng], {
         icon: L.divIcon({
            className: 'custom-map-marker',
            html: '<i class="fas fa-flag-checkered" style="color: #00b894; font-size: 20px;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
         })
      }).addTo(map).bindPopup(`<b>Destination</b><br>${trackingData.destinationLocation.name}`);
      
      // Add current location marker
      trackingMarker = L.marker([trackingData.currentLocation.lat, trackingData.currentLocation.lng], {
         icon: L.divIcon({
            className: 'custom-map-marker pulse',
            html: '<i class="fas fa-truck" style="color: #fd79a8; font-size: 20px;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
         })
      }).addTo(map).bindPopup(`<b>Current Location</b><br>${trackingData.currentLocation.name}`);
      
      // Add animation for the marker
      animateMarker();
   }
   
   // Animate marker for visual effect
   function animateMarker() {
      if (trackingMarker) {
         const icon = trackingMarker.getElement();
         if (icon) {
            icon.style.transition = 'transform 0.5s ease-in-out';
            
            // Small bounce animation
            setTimeout(() => {
               icon.style.transform = 'translateY(-5px)';
               
               setTimeout(() => {
                  icon.style.transform = 'translateY(0)';
               }, 500);
            }, 100);
         }
      }
   }
   
   // Format date helper
   function formatDate(date) {
      return date.toLocaleDateString('en-US', {
         year: 'numeric',
         month: 'short',
         day: 'numeric',
         hour: '2-digit',
         minute: '2-digit'
      });
   }
   
   // Close modal when clicking outside
   window.addEventListener('click', function(event) {
      const modal = document.getElementById('tracking-modal');
      if (event.target === modal) {
         closeTrackingModal();
      }
   });
   
   // Close modal with Escape key
   window.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
         closeTrackingModal();
      }
   });
</script>



<!-- filepath: c:\xampp\htdocs\BookCraft\orders.php -->
<!-- Modern Order Receipt Modal -->
<div id="receipt-modal" class="receipt-modal">
   <div class="receipt-content">
      <div class="receipt-header">
         <div class="receipt-title">
            <i class="fas fa-receipt"></i> Order Receipt
         </div>
         <button class="modal-close" onclick="closeReceiptModal()" aria-label="Close">
            <i class="fas fa-times"></i>
         </button>
      </div>
      <div class="receipt-body" id="receipt-container">
         <!-- Receipt content will be populated here -->
         <div class="receipt-loader">
            <div class="spinner"></div>
            <p>Loading receipt...</p>
         </div>
      </div>
      <div class="receipt-footer">
         <button class="modal-btn secondary" onclick="closeReceiptModal()">Close</button>
         <button class="modal-btn primary" onclick="printReceipt()">
            <i class="fas fa-print"></i> Print Receipt
         </button>
      </div>
   </div>
</div>

<script>
// Order receipt functionality
let currentReceiptOrderId = null;
let logoBase64 = null;

// Preload and convert logo to base64 on page load
document.addEventListener('DOMContentLoaded', function() {
   // Initialize logo preloading
   preloadLogo();
   
   // Find all print receipt buttons and update their event handlers
   const printButtons = document.querySelectorAll('.action-btn.btn-outline');
   printButtons.forEach(button => {
      const orderItem = button.closest('.order-item');
      const orderIdLink = orderItem.querySelector('a[href*="order_details.php?id="]');
      if (orderIdLink) {
         const orderId = orderIdLink.href.split('id=')[1];
         button.onclick = function(e) {
            e.preventDefault();
            openReceiptModal(orderId);
         };
      }
   });
});

// Preload logo and convert to base64
function preloadLogo() {
   const img = new Image();
   img.crossOrigin = "Anonymous";
   img.onload = function() {
      try {
         const canvas = document.createElement('canvas');
         canvas.width = this.width;
         canvas.height = this.height;
         const ctx = canvas.getContext('2d');
         ctx.drawImage(this, 0, 0);
         logoBase64 = canvas.toDataURL('data:image/jpeg;base64,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');
         console.log("Logo preloaded successfully");
      } catch (e) {
         console.error("Error converting logo to base64:", e);
      }
   };
   img.onerror = function() {
      console.warn("Primary logo failed to load, trying fallback...");
      img.src = 'favicon.png';
   };
   img.src = 'logo.png';
}

function openReceiptModal(orderId) {
   currentReceiptOrderId = orderId;
   const modal = document.getElementById('receipt-modal');
   modal.classList.add('active');
   document.body.style.overflow = 'hidden';
   
   // Show loading state
   document.getElementById('receipt-container').innerHTML = `
      <div class="receipt-loader">
         <div class="spinner"></div>
         <p>Loading your receipt...</p>
      </div>
   `;
   
   // Find the order item element
   const orderElement = document.querySelector(`.order-item:has(a[href="order_details.php?id=${orderId}"])`);
   if (!orderElement) return;
   
   // Extract order details
   const orderDate = orderElement.querySelector('.order-date').textContent.trim().replace('️', '');
   const orderName = orderElement.querySelector('.detail-item:nth-child(1) .detail-value').textContent.trim();
   const paymentMethod = orderElement.querySelector('.detail-item:nth-child(2) .detail-value').textContent.trim();
   const address = orderElement.querySelector('.detail-item:nth-child(3) .detail-value').textContent.trim();
   const email = orderElement.querySelector('.detail-item:nth-child(4) .detail-value').textContent.trim();
   const totalPrice = orderElement.querySelector('.order-price').textContent.trim();
   const itemCount = orderElement.querySelector('.item-count').textContent.trim();
   const status = orderElement.querySelector('.status-text').textContent.trim();
   
   // Fetch additional order details via AJAX
   fetchOrderDetails(orderId, function(orderDetails) {
      // Determine logo source - use base64 if available, otherwise use the path with fallback
      const logoSrc = logoBase64 || `${window.location.origin} logo.png`;
      const fallbackSrc = `${window.location.origin}/BookCraft/images/favicon.png`;
      
      // Generate items list HTML
      let itemsHtml = '';
      if (orderDetails && orderDetails.products) {
         itemsHtml = `
            <div class="receipt-items">
               <h4 class="receipt-section-title">
                  <i class="fas fa-shopping-basket receipt-section-icon"></i>
                  Order Items
               </h4>
               <div class="receipt-table-container">
                  <table class="items-table">
                     <thead>
                        <tr>
                           <th>Item</th>
                           <th>Qty</th>
                           <th>Price</th>
                           <th>Total</th>
                        </tr>
                     </thead>
                     <tbody>
         `;
         
         let subtotal = 0;
         orderDetails.products.forEach(product => {
            const quantity = parseInt(product.quantity);
            const price = parseFloat(product.price);
            const productTotal = (price * quantity).toFixed(2);
            subtotal += parseFloat(productTotal);
            
            itemsHtml += `
               <tr>
                  <td><span class="product-name">${product.name}</span></td>
                  <td>${quantity}</td>
                  <td>$${price.toFixed(2)}</td>
                  <td>$${productTotal}</td>
               </tr>
            `;
         });
         
         itemsHtml += `
                     </tbody>
                  </table>
               </div>
            </div>
         `;
      }
      
      // Extract phone number if available
      const phone = orderDetails && orderDetails.phone ? orderDetails.phone : 'N/A';
      
      // Format order date and time
      const formattedDateTime = orderDetails && orderDetails.placed_on ? 
         new Date(orderDetails.placed_on).toLocaleString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
         }) : orderDate;
      
      // Status badge
      const statusClass = status.toLowerCase() === 'completed' ? 'status-completed' : 'status-pending';
      const statusIcon = status.toLowerCase() === 'completed' ? 'fa-check-circle' : 'fa-clock';
      
      // Generate receipt HTML
      const receiptHTML = `
         <div class="receipt-wrapper">
            <div class="receipt-header-area">
               <div class="receipt-logo">
                  <img src="${logoSrc}" alt="BookCraft Logo" onerror="this.src='${fallbackSrc}'; this.onerror='';">
                  <h2>BookCraft</h2>
               </div>
               
               <div class="receipt-badge ${statusClass}">
                  <i class="fas ${statusIcon}"></i> ${status}
               </div>
            </div>
            
            <div class="receipt-divider"></div>
            
            <div class="receipt-order-header">
               <div class="receipt-order-id-container">
                  <h3>ORDER RECEIPT</h3>
                  <div class="receipt-order-id">#${orderId}</div>
               </div>
               <div class="receipt-order-date">
                  <i class="far fa-calendar-alt"></i> ${formattedDateTime}
               </div>
            </div>
            
            <div class="receipt-divider"></div>
            
            <div class="receipt-customer-info">
               <h4 class="receipt-section-title">
                  <i class="fas fa-user receipt-section-icon"></i>
                  Customer Information
               </h4>
               <div class="receipt-info-grid">
                  <div class="receipt-info-item">
                     <span class="receipt-label">Name</span>
                     <span class="receipt-value">${orderName}</span>
                  </div>
                  <div class="receipt-info-item">
                     <span class="receipt-label">Email</span>
                     <span class="receipt-value">${email}</span>
                  </div>
                  <div class="receipt-info-item">
                     <span class="receipt-label">Phone</span>
                     <span class="receipt-value">${phone}</span>
                  </div>
                  <div class="receipt-info-item">
                     <span class="receipt-label">Address</span>
                     <span class="receipt-value">${address}</span>
                  </div>
                  <div class="receipt-info-item">
                     <span class="receipt-label">Payment Method</span>
                     <span class="receipt-value payment-method">
                        <i class="fas fa-${paymentMethod.toLowerCase().includes('card') ? 'credit-card' : 'money-bill-wave'}"></i>
                        ${paymentMethod}
                     </span>
                  </div>
                  <div class="receipt-info-item">
                     <span class="receipt-label">Order Status</span>
                     <span class="receipt-value status-pill ${statusClass.replace('status-', '')}">
                        <i class="fas ${statusIcon}"></i> ${status}
                     </span>
                  </div>
               </div>
            </div>
            
            <div class="receipt-divider"></div>
            
            ${itemsHtml}
            
            <div class="receipt-divider"></div>
            
            <div class="receipt-summary">
               <div class="receipt-summary-row receipt-subtotal">
                  <span class="receipt-summary-label">Subtotal</span>
                  <span class="receipt-summary-value">${totalPrice}</span>
               </div>
               <div class="receipt-summary-row receipt-shipping">
                  <span class="receipt-summary-label">Shipping</span>
                  <span class="receipt-summary-value">$0.00</span>
               </div>
               <div class="receipt-summary-row receipt-tax">
                  <span class="receipt-summary-label">Tax</span>
                  <span class="receipt-summary-value">Included</span>
               </div>
               <div class="receipt-divider small"></div>
               <div class="receipt-summary-row receipt-total">
                  <span class="receipt-summary-label">Total Amount</span>
                  <span class="receipt-summary-value">${totalPrice}</span>
               </div>
            </div>
            
            <div class="receipt-divider"></div>
            
            <div class="receipt-footer-text">
               <p>Thank you for shopping with BookCraft!</p>
               <p>For any inquiries, please contact us at <a href="mailto:support@bookcraft.com">support@bookcraft.com</a></p>
            </div>
            
            <div class="receipt-barcode">
               <div class="barcode-text">*BC${orderId.toString().padStart(6, '0')}*</div>
            </div>
         </div>
      `;
      
      // Update receipt container with animation
      const container = document.getElementById('receipt-container');
      container.style.opacity = 0;
      setTimeout(() => {
         container.innerHTML = receiptHTML;
         container.style.opacity = 1;
      }, 300);
   });
}

// Fetch order details via AJAX
function fetchOrderDetails(orderId, callback) {
   // First try to get the products directly from the page
   const orderElement = document.querySelector(`.order-item:has(a[href="order_details.php?id=${orderId}"])`);
   if (!orderElement) {
      callback(null);
      return;
   }
   
   // Make an AJAX request to get detailed order information
   const xhr = new XMLHttpRequest();
   xhr.open('GET', `get_order_details.php?id=${orderId}`, true);
   xhr.onload = function() {
      if (this.status === 200) {
         try {
            const response = JSON.parse(this.responseText);
            callback(response);
         } catch (e) {
            console.error('Error parsing order details:', e);
            // Fallback to dummy data if AJAX fails
            const dummyData = generateDummyOrderDetails(orderId);
            callback(dummyData);
         }
      } else {
         console.error('Failed to fetch order details');
         // Fallback to dummy data if AJAX fails
         const dummyData = generateDummyOrderDetails(orderId);
         callback(dummyData);
      }
   };
   xhr.onerror = function() {
      console.error('Request error');
      // Fallback to dummy data if AJAX fails
      const dummyData = generateDummyOrderDetails(orderId);
      callback(dummyData);
   };
   xhr.send();
}

// Generate dummy order details if AJAX fails
function generateDummyOrderDetails(orderId) {
   const orderElement = document.querySelector(`.order-item:has(a[href="order_details.php?id=${orderId}"])`);
   if (!orderElement) return null;
   
   const totalPrice = orderElement.querySelector('.order-price').textContent.trim().replace('$', '');
   const itemCountText = orderElement.querySelector('.item-count').textContent.trim();
   const itemCount = parseInt(itemCountText.match(/\d+/)[0]) || 1;
   
   // Generate dummy products based on item count and total price
   const products = [];
   const basePrice = parseFloat(totalPrice) / itemCount;
   
   for (let i = 0; i < itemCount; i++) {
      // Generate random book titles for a better demo experience
      const bookTitles = [
         "The Great Adventure",
         "Mystery of the Lost Key",
         "Programming Essentials",
         "Web Development Guide",
         "Data Structures & Algorithms",
         "The Art of Fiction",
         "History of Science",
         "Cooking Masterclass",
         "Financial Freedom",
         "The Psychology of Success"
      ];
      
      const randomTitle = bookTitles[Math.floor(Math.random() * bookTitles.length)];
      
      products.push({
         name: randomTitle,
         quantity: 1,
         price: basePrice.toFixed(2)
      });
   }
   
   // Generate a random phone number
   const generatePhone = () => {
      const prefixes = ['+1', '+44', '+61', '+91', '+94'];
      const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
      const number = Math.floor(Math.random() * 9000000000) + 1000000000;
      return `${prefix} ${number.toString().substring(0, 3)}-${number.toString().substring(3, 6)}-${number.toString().substring(6)}`;
   };
   
   return {
      phone: generatePhone(),
      placed_on: new Date().toISOString(),
      products: products
   };
}

function closeReceiptModal() {
   const modal = document.getElementById('receipt-modal');
   modal.classList.add('fade-out');
   setTimeout(() => {
      modal.classList.remove('active');
      modal.classList.remove('fade-out');
      document.body.style.overflow = 'auto';
   }, 300);
}

function printReceipt() {
   const receiptContainer = document.getElementById('receipt-container');
   
   // Show print preparation indicator
   const printPrep = document.createElement('div');
   printPrep.className = 'print-preparation';
   printPrep.innerHTML = `
      <div class="spinner"></div>
      <p>Preparing your receipt for printing...</p>
   `;
   document.body.appendChild(printPrep);
   
   // Create a new window with only the receipt content
   const printWindow = window.open('', '_blank', 'height=600,width=800');
   printWindow.document.write('<html><head><title>Order Receipt - BookCraft</title>');
   
   // Add necessary styles
   printWindow.document.write(`
      <style>
         @media print {
            @page {
               size: A4;
               margin: 10mm;
            }
         }
         
         body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            background-color: #fff;
         }
         
         .receipt-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
         }
         
         .receipt-header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
         }
         
         .receipt-logo {
            display: flex;
            align-items: center;
         }
         
         .receipt-logo img {
            height: 50px;
            margin-right: 15px;
         }
         
         .receipt-logo h2 {
            font-size: 28px;
            color: #6c5ce7;
            margin: 0;
            font-weight: 700;
         }
         
         .receipt-badge {
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
         }
         
         .status-completed {
            background-color: #e6f7e6;
            color: #00b894;
         }
         
         .status-pending {
            background-color: #fff7e6;
            color: #fdcb6e;
         }
         
         .receipt-divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
         }
         
         .receipt-divider.small {
            margin: 10px 0;
         }
         
         .receipt-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
         }
         
         .receipt-order-id-container {
            text-align: left;
         }
         
         .receipt-order-id-container h3 {
            font-size: 20px;
            margin: 0 0 5px 0;
            color:rgb(33, 4, 252);
            font-weight: 600;
         }
         
         .receipt-order-id {
            font-size: 18px;
            font-weight: 700;
            color:rgb(255, 9, 9);
         }
         
         .receipt-order-date {
            font-size: 14px;
            color:rgb(1, 1, 8);
            display: flex;
            align-items: center;
            gap: 6px;
         }
         
         .receipt-section-title {
            font-size: 18px;
            font-weight: 600;
            color:rgb(19, 19, 21);
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
         }
         
         .receipt-section-icon {
            color:rgb(4, 247, 52);
         }
         
         .receipt-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 10px;
         }
         
         .receipt-info-item {
            padding: 10px;
            background-color:rgb(199, 219, 248);
            border-radius: 8px;
         }
         
         .receipt-label {
            display: block;
            font-size: 12px;
            color:rgb(5, 21, 68);
            margin-bottom: 4px;
            font-weight: 500;
         }
         
         .receipt-value {
            font-weight: 600;
            color:rgb(6, 6, 7);
            font-size: 15px;
         }
         
         .payment-method {
            display: flex;
            align-items: center;
            gap: 6px;
         }
         
         .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            color:  #6c5ce7
         }
         
         .completed {
            background-color: #e6f7e6;
            color: #00b894;
         }
         
         .pending {
            background-color: #fff7e6;
            color: #fdcb6e;
         }
         
         .receipt-table-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
         }
         
         .items-table {
            width: 100%;
            border-collapse: collapse;
         }
         
         .items-table th, .items-table td {
            padding: 12px 15px;
            text-align: left;
         }
         
         .items-table th {
            background-color: #f3f1ff;
            color: #333;
            font-weight: 600;
            font-size: 14px;
         }
         
         .items-table tr:nth-child(even) td {
            background-color: #f9f9f9;
         }
         
         .items-table tr:hover td {
            background-color: #f3f1ff;
         }
         
         .product-name {
            font-weight: 500;
            color:  #6c5ce7
         }
         
         .receipt-summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
         }
         
         .receipt-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
         }
         
         .receipt-summary-label {
            color: #666;
            font-size: 14px;
            color:rgb(17, 15, 15)
         }
         
         .receipt-summary-value {
            font-weight: 600;
            color:rgb(18, 18, 19)
         }
         
         .receipt-total .receipt-summary-label,
         .receipt-total .receipt-summary-value {
            font-size: 18px;
            font-weight: 700;
            color:rgb(14, 4, 89)
         }
         
         .receipt-total .receipt-summary-value {
            color:rgb(11, 10, 21);
         }
         
         .receipt-footer-text {
            text-align: center;
            color:rgb(26, 5, 183)
            font-size: 14px;
            margin: 20px 0;
            line-height: 1.5;
         }
         
         .receipt-footer-text a {
            color:rgb(14, 14, 15);
            text-decoration: none;
         }
         
         .receipt-barcode {
            text-align: center;
            margin-top: 30px;
         }
         
         .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color:rgb(244, 20, 31)
            letter-spacing: 5px;
            padding: 10px 20px;
            display: inline-block;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f9f9f9;
         }

         .print-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
         }
         
         .print-button {
            padding: 12px 24px;
            background-color:rgb(224, 222, 236);
            color: blue;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
         }
         
         .print-button:hover {
            background-color:rgb(237, 236, 246);
         }
         
         .close-button {
            padding: 12px 24px;
            background-color: #f1f1f1;
            color:  #6c5ce7
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
         }
         
         .close-button:hover {
            background-color: #e6e6e6;
         }

         @media print {
            body {
               padding: 0;
               margin: 0;
            }
            
            .receipt-wrapper {
               border: none;
               box-shadow: none;
               padding: 0;
               width: 100%;
            }
            
            .print-buttons {
               display: none;
            }
         }
      </style>
   `);
   
   // Add the receipt content
   printWindow.document.write('</head><body>');
   printWindow.document.write(receiptContainer.innerHTML);
   
   // Add print buttons visible only in browser
   printWindow.document.write(`
      <div class="print-buttons">
         <button class="print-button" onclick="window.print();">
            <span style="font-size: 18px;">🖨️</span> Print Receipt
         </button>
         <button class="close-button" onclick="window.close();">
            Close Window
         </button>
      </div>
   `);
   
   printWindow.document.write('</body></html>');
   printWindow.document.close();
   
   // Remove the preparation indicator
   setTimeout(() => {
      document.body.removeChild(printPrep);
      printWindow.focus();
   }, 800);
   
   // Wait for content and images to load before printing
   printWindow.onload = function() {
      const images = printWindow.document.images;
      let imagesToLoad = images.length;
      
      if (imagesToLoad === 0) {
         // No images to load, print immediately
         setTimeout(() => {
            printWindow.focus();
         }, 500);
         return;
      }
      
      // Counter for loaded images
      let loadedImages = 0;
      
      // Function to check if all images are loaded
      const checkAllImagesLoaded = function() {
         loadedImages++;
         if (loadedImages >= imagesToLoad) {
            setTimeout(() => {
               printWindow.focus();
            }, 500);
         }
      };
      
      // Set up load events for all images
      for (let i = 0; i < images.length; i++) {
         // If image is already loaded or has error
         if (images[i].complete) {
            checkAllImagesLoaded();
         } else {
            images[i].onload = checkAllImagesLoaded;
            images[i].onerror = checkAllImagesLoaded;
         }
      }
      
      // Fallback - focus after 2 seconds even if images aren't loaded
      setTimeout(function() {
         if (loadedImages < imagesToLoad) {
            console.warn("Not all images loaded, focusing anyway...");
            printWindow.focus();
         }
      }, 2000);
   };
}

// Close receipt modal when clicking outside
window.addEventListener('click', function(event) {
   const modal = document.getElementById('receipt-modal');
   if (event.target === modal) {
      closeReceiptModal();
   }
});

// Close receipt modal with Escape key
window.addEventListener('keydown', function(event) {
   if (event.key === 'Escape') {
      closeReceiptModal();
   }
});
</script>

<style>
/* Modern Receipt Modal Styles */
.receipt-modal {
   display: none;
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0, 0, 0, 0.45);
   z-index: 1000;
   justify-content: center;
   align-items: center;
   opacity: 0;
   transition: opacity 0.3s ease;
}

.receipt-modal.active {
   display: flex;
   animation: fadeIn 0.3s forwards;
}

.receipt-modal.fade-out {
   animation: fadeOut 0.3s forwards;
}

@keyframes fadeIn {
   from { opacity: 0; }
   to { opacity: 1; }
}

@keyframes fadeOut {
   from { opacity: 1; }
   to { opacity: 0; }
}

.receipt-content {
   background-color: var(--text-white);
   border-radius: var(--radius-lg);
   box-shadow: var(--shadow-lg), 0 10px 40px rgba(0, 0, 0, 0.15);
   width: 90%;
   max-width: 800px;
   max-height: 90vh;
   overflow: hidden;
   display: flex;
   flex-direction: column;
   animation: slideUp 0.4s cubic-bezier(0.19, 1, 0.22, 1) forwards;
   transform: translateY(20px);
   opacity: 0;
}

@keyframes slideUp {
   from {
      transform: translateY(20px);
      opacity: 0;
   }
   to {
      transform: translateY(0);
      opacity: 1;
   }
}

.receipt-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   padding: 20px 25px;
   border-bottom: 1px solid rgba(255, 251, 251, 0.99);
   background-color: var(--primary-color);
   color: white;
}

.receipt-title {
   font-size: 1.9rem;
   font-weight: 700;
   color: white;
   display: flex;
   align-items: center;
   gap: 12px;
}

.receipt-title i {
   font-size: 1.2rem;
   background-color: rgba(255, 255, 255, 0.2);
   width: 36px;
   height: 36px;
   display: flex;
   align-items: center;
   justify-content: center;
   border-radius: 50%;
}

.modal-close {
   background: none;
   border: none;
   font-size: 1.2rem;
   color: rgba(5, 56, 39, 0.8);
   cursor: pointer;
   transition: var(--transition);
   width: 36px;
   height: 36px;
   display: flex;
   align-items: center;
   justify-content: center;
   border-radius: 50%;
}

.modal-close:hover {
   background-color: rgba(255, 255, 255, 0.2);
   color: white;
}

.receipt-body {
   padding: 25px;
   overflow-y: auto;
   transition: opacity 0.3s ease;
}

.receipt-footer {
   padding: 15px 25px;
   border-top: 1px solid rgba(0, 0, 0, 0.05);
   display: flex;
   justify-content: flex-end;
   gap: 12px;
   background-color: #f9f9f9;
}

.modal-btn {
   padding: 10px 20px;
   border-radius: 30px;
   font-weight: 600;
   cursor: pointer;
   transition: var(--transition);
   font-size: 1.5rem;
   display: flex;
   align-items: center;
   gap: 8px;
}

.modal-btn.primary {
   background-color: var(--primary-color);
   color: var(--text-white);
   border: none;
   box-shadow: 0 4px 12px rgba(108, 92, 231, 0.2);
}

.modal-btn.primary:hover {
   background-color: #5849e3;
   transform: translateY(-2px);
   box-shadow: 0 6px 16px rgba(108, 92, 231, 0.3);
}

.modal-btn.secondary {
   background-color: transparent;
   border: 1px solid #e0e0e0;
   color: var(--text-dark);
}

.modal-btn.secondary:hover {
   border-color: var(--primary-color);
   color: var(--primary-color);
   background-color: rgba(108, 92, 231, 0.05);
}

/* Receipt Loader */
.receipt-loader {
   display: flex;
   flex-direction: column;
   align-items: center;
   justify-content: center;
   padding: 50px 0;
   color: var(--text-light);
}

.spinner {
   width: 40px;
   height: 40px;
   border: 4px solid rgba(108, 92, 231, 0.1);
   border-radius: 50%;
   border-top-color: var(--primary-color);
   animation: spin 1s linear infinite;
   margin-bottom: 15px;
}

@keyframes spin {
   0% { transform: rotate(0deg); }
   100% { transform: rotate(360deg); }
}

/* Print Preparation */
.print-preparation {
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0, 0, 0, 0.7);
   display: flex;
   flex-direction: column;
   justify-content: center;
   align-items: center;
   z-index: 2000;
   color: white;
   animation: fadeIn 0.3s forwards;
}

.print-preparation .spinner {
   border-color: rgba(255, 255, 255, 0.2);
   border-top-color: white;
   width: 50px;
   height: 50px;
}

.print-preparation p {
   margin-top: 15px;
   font-size: 1.4rem;
   color: dark;
}

/* Modern Receipt Styles */
.receipt-wrapper {
   max-width: 800px;
   margin: 0 auto;
   padding: 25px;
   background-color: white;
   border-radius: var(--radius-md);
   box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.receipt-header-area {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 20px;
}

.receipt-logo {
   display: flex;
   align-items: center;
}

.receipt-logo img {
   height: 50px;
   margin-right: 15px;
   border-radius: 8px;
   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.receipt-logo h2 {
   font-size: 28px;
   color: var(--dark-color);
   margin: 0;
   font-weight: 700;
}

.receipt-badge {
   padding: 8px 12px;
   border-radius: 30px;
   font-size: 14px;
   font-weight: 600;
   color: var(--dark-color);
   display: flex;
   align-items: center;
   gap: 6px;
}

.status-completed {
   background-color: rgba(0, 184, 148, 0.1);
   color: var(--success-color);
}

.status-pending {
   background-color: rgba(253, 203, 110, 0.1);
   color: var(--warning-color);
}

.receipt-divider {
   height: 1px;
   background-color: #e0e0e0;
   margin: 20px 0;
}

.receipt-divider.small {
   margin: 10px 0;
}

.receipt-order-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 10px;
}

.receipt-order-id-container {
   text-align: left;
}

.receipt-order-id-container h3 {
   font-size: 20px;
   margin: 0 0 5px 0;
   color: var(--text-dark);
   font-weight: 600;
}

.receipt-order-id {
   font-size: 18px;
   font-weight: 700;
   color: var(-text-dark);
}

.receipt-order-date {
   font-size: 14px;
   color: var(-text-dark);
   display: flex;
   align-items: center;
   gap: 6px;
}

.receipt-section-title {
   font-size: 18px;
   font-weight: 600;
   color: var(--text-dark);
   margin: 0 0 15px 0;
   display: flex;
   align-items: center;
   gap: 8px;
}

.receipt-section-icon {
   color: var(--primary-color);
}

.receipt-info-grid {
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   gap: 15px;
   margin-bottom: 10px;
}

.receipt-info-item {
   padding: 10px;
   background-color: #f9f9f9;
   border-radius: 8px;
   transition: all 0.3s ease;
}

.receipt-info-item:hover {
   background-color: #f3f1ff;
   box-shadow: 0 2px 6px rgba(247, 247, 253, 0.97);
}

.receipt-label {
   display: block;
   font-size: 13px;
   color: var( --success-color);
   margin-bottom: 4px;
   font-weight: 500;
}

.receipt-value {
   font-weight: 450;
   color: var(--text-dark);
   font-size: 15px;
}

.payment-method {
   display: flex;
   align-items: center;
   gap: 6px;
}

.status-pill {
   display: inline-flex;
   align-items: center;
   gap: 5px;
   padding: 4px 8px;
   border-radius: 30px;
   font-size: 13px;
   font-weight: 600;
}

.completed {
   background-color: rgba(0, 184, 148, 0.1);
   color: var(--success-color);
}

.pending {
   background-color: rgba(253, 203, 110, 0.1);
   color: var(--warning-color);
}

.receipt-table-container {
   border-radius: 8px;
   overflow: hidden;
   border: 1px solid #e0e0e0;
   box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.items-table {
   width: 100%;
   border-collapse: collapse;
}

.items-table th, .items-table td {
   padding: 12px 15px;
   text-align: left;
}

.items-table th {
   background-color: #f3f1ff;
   color: var(--text-dark);
   font-weight: 600;
   font-size: 14px;
}

.items-table tr:nth-child(even) td {
   background-color: #f9f9f9;
}

.items-table tr:hover td {
   background-color: #f3f1ff;
}

.product-name {
   font-weight: 500;
}

.receipt-summary {
   margin: 20px 0;
   padding: 15px;
   background-color: #f9f9f9;
   border-radius: 8px;
   box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.receipt-summary-row {
   display: flex;
   justify-content: space-between;
   margin-bottom: 10px;
}

.receipt-summary-label {
   color: var(--text-dark);
   font-size: 14px;
}

.receipt-summary-value {
   font-weight: 600;
   color: var(--text-dark);
}

.receipt-total .receipt-summary-label {
      font-size: 18px;
   font-weight: 700;
   color: var(--text-dark);
}

.receipt-total .receipt-summary-value {
   font-size: 18px;
   font-weight: 700;
   color: var(--text-dark);
}

.receipt-total .receipt-summary-value {
   color: var(--primary-color);
}

.receipt-footer-text {
   text-align: center;
   color: var(--text-dark);
   font-size: 14px;
   margin: 20px 0;
   line-height: 1.5;
}

.receipt-footer-text a {
   color: var(--primary-color);
   text-decoration: none;
   transition: color 0.3s ease;
}

.receipt-footer-text a:hover {
   color: #5849e3;
   text-decoration: underline;
}

.receipt-barcode {
   text-align: center;
   margin-top: 30px;
}

.barcode-text {
   font-family: 'Courier New', monospace;
   font-size: 16px;
   letter-spacing: 5px;
   padding: 10px 20px;
   display: inline-block;
   border: 1px solid #e0e0e0;
   border-radius: 5px;
   background-color: #f9f9f9;
}

@media (max-width: 768px) {
   .receipt-info-grid {
      grid-template-columns: 1fr;
   }
   
   .receipt-order-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
   }
   
   .receipt-header-area {
      flex-direction: column;
      gap: 15px;
   }
   
   .receipt-logo {
      justify-content: center;
      width: 100%;
   }
   
   .receipt-badge {
      align-self: center;
   }
}

@media (max-width: 576px) {
 .receipt-body {
      padding: 15px;
   }
   
   .receipt-wrapper {
      padding: 15px;
   }
   
   .receipt-logo h2 {
      font-size: 22px;
   }
   
   .receipt-order-id-container h3 {
      font-size: 18px;
   }
   
   .receipt-order-id {
      font-size: 16px;
   }
   
   .receipt-total .receipt-summary-label,
   .receipt-total .receipt-summary-value {
      font-size: 16px;
   }
}


/* Print-specific styles */
@media print {
   body * {
      visibility: hidden;
   }
   
   .receipt-wrapper, .receipt-wrapper * {
      visibility: visible;
   }
   
   .receipt-wrapper {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      border: none;
      box-shadow: none;
   }
}
</style>