<?php

include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

// Initialize message array with a different name to avoid conflicts
$order_messages = [];

// Update order status
if(isset($_POST['update_order'])){
   $order_update_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'];
   
   // Use prepared statement to prevent SQL injection
   $stmt = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $stmt->bind_param("si", $update_payment, $order_update_id);
   
   if($stmt->execute()){
      $order_messages[] = 'Payment status has been updated!';
   } else {
      $order_messages[] = 'Failed to update payment status!';
   }
}

// Delete order
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   
   // Use prepared statement
   $stmt = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $stmt->bind_param("i", $delete_id);
   
   if($stmt->execute()){
      $order_messages[] = 'Order has been deleted!';
      // Redirect to avoid resubmission
      header('location:admin_orders.php');
      exit();
   } else {
      $order_messages[] = 'Failed to delete order!';
   }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Build query based on filters
$query = "SELECT * FROM `orders` WHERE 1=1";

if($status_filter != 'all') {
    $query .= " AND payment_status = '$status_filter'";
}

if(!empty($search_query)) {
    $query .= " AND (name LIKE '%$search_query%' OR email LIKE '%$search_query%' OR address LIKE '%$search_query%')";
}

// Apply sorting
if($sort_by == 'newest') {
    $query .= " ORDER BY placed_on DESC";
} elseif($sort_by == 'oldest') {
    $query .= " ORDER BY placed_on ASC";
} elseif($sort_by == 'highest') {
    $query .= " ORDER BY total_price DESC";
} elseif($sort_by == 'lowest') {
    $query .= " ORDER BY total_price ASC";
}

// Count total matching records for pagination
$total_query = mysqli_query($conn, $query);
$total_records = mysqli_num_rows($total_query);
$total_pages = ceil($total_records / $results_per_page);

// Add pagination to query
$query .= " LIMIT $offset, $results_per_page";
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Orders | BookCraft Admin</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <!-- Custom admin CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">
   
   <!-- Additional custom styles -->
   <style>
      .dashboard-container {
         padding: 2rem;
         background-color: #f8f9fa;
         min-height: 100vh;
      }
      
      .card {
         border-radius: 10px;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
         margin-bottom: 1.5rem;
         border: none;
      }
      
      .card-header {
         background-color: #fff;
         border-bottom: 1px solid #e9ecef;
         padding: 1rem 1.5rem;
         font-weight: 600;
      }
      
      .filter-bar {
         background-color: #fff;
         padding: 1rem;
         border-radius: 10px;
         margin-bottom: 1.5rem;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      }
      
      .status-badge {
         padding: 0.4rem 0.8rem;
         border-radius: 30px;
         font-size: 0.8rem;
         font-weight: 500;
      }
      
      .status-pending {
         background-color: #fff3cd;
         color: #856404;
      }
      
      .status-completed {
         background-color: #d4edda;
         color: #155724;
      }
      
      .order-detail-row {
         padding: 0.75rem 0;
         border-bottom: 1px solid #f0f0f0;
      }
      
      .order-detail-row:last-child {
         border-bottom: none;
      }
      
      .alert {
         border-radius: 8px;
      }
      
      .pagination-container {
         display: flex;
         justify-content: center;
         margin-top: 2rem;
      }
      
      .page-link {
         color: #4a4a4a;
         border: none;
         margin: 0 3px;
      }
      
      .page-link:hover, .page-link:focus {
         background-color: #f1f1f1;
         color: #333;
      }
      
      .page-item.active .page-link {
         background-color: #555;
         border-color: #555;
      }
      
      @media (max-width: 768px) {
         .dashboard-container {
            padding: 1rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<div class="dashboard-container">
   <!-- Custom message display - using our separate order_messages array -->
   <?php if(!empty($order_messages) && is_array($order_messages)): ?>
      <?php foreach($order_messages as $msg): ?>
         <div class="alert alert-<?php echo strpos($msg, 'Failed') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
            <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      <?php endforeach; ?>
   <?php endif; ?>

   <div class="row mb-4">
      <div class="col">
         <h1 class="h3 mb-0">Manage Orders</h1>
         <p class="text-muted">View and manage customer orders</p>
      </div>
   </div>
   
   <!-- Filter and Search Section -->
   <div class="card filter-bar mb-4">
      <div class="card-body">
         <form action="" method="GET" class="row g-3">
            <div class="col-md-4">
               <label for="search" class="form-label">Search</label>
               <div class="input-group">
                  <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, email..." value="<?php echo htmlspecialchars($search_query); ?>">
                  <button class="btn btn-outline-secondary" type="submit">
                     <i class="fas fa-search"></i>
                  </button>
               </div>
            </div>
            
            <div class="col-md-3">
               <label for="status" class="form-label">Payment Status</label>
               <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                  <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Orders</option>
                  <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
               </select>
            </div>
            
            <div class="col-md-3">
               <label for="sort" class="form-label">Sort By</label>
               <select class="form-select" id="sort" name="sort" onchange="this.form.submit()">
                  <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                  <option value="oldest" <?php echo $sort_by == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                  <option value="highest" <?php echo $sort_by == 'highest' ? 'selected' : ''; ?>>Highest Price</option>
                  <option value="lowest" <?php echo $sort_by == 'lowest' ? 'selected' : ''; ?>>Lowest Price</option>
               </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
               <a href="admin_orders.php" class="btn btn-outline-secondary w-100">Reset Filters</a>
            </div>
         </form>
      </div>
   </div>
   
   <!-- Orders Section -->
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <span>Order List</span>
         <span class="badge bg-secondary"><?php echo $total_records; ?> orders found</span>
      </div>
      <div class="card-body p-0">
         <?php if($total_records > 0): ?>
            <div class="table-responsive">
               <table class="table table-hover mb-0">
                  <thead class="table-light">
                     <tr>
                        <th scope="col">#ID</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Date</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Payment</th>
                        <th scope="col">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $select_orders = mysqli_query($conn, $query) or die('query failed');
                     while($fetch_orders = mysqli_fetch_assoc($select_orders)):
                     ?>
                     <tr>
                        <td><?php echo $fetch_orders['id']; ?></td>
                        <td>
                           <div class="fw-bold"><?php echo htmlspecialchars($fetch_orders['name']); ?></div>
                           <div class="small text-muted"><?php echo htmlspecialchars($fetch_orders['email']); ?></div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($fetch_orders['placed_on'])); ?></td>
                        <td>Rs<?php echo number_format($fetch_orders['total_price'], 2); ?></td>
                        <td>
                           <span class="status-badge <?php echo $fetch_orders['payment_status'] == 'completed' ? 'status-completed' : 'status-pending'; ?>">
                              <?php echo ucfirst($fetch_orders['payment_status']); ?>
                           </span>
                        </td>
                        <td><?php echo ucfirst($fetch_orders['method']); ?></td>
                        <td>
                           <div class="d-flex gap-2">
                              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $fetch_orders['id']; ?>">
                                 <i class="fas fa-eye"></i>
                              </button>
                              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $fetch_orders['id']; ?>">
                                 <i class="fas fa-trash"></i>
                              </button>
                           </div>
                        </td>
                     </tr>
                     
                     <!-- Order Details Modal -->
                     <div class="modal fade" id="orderModal<?php echo $fetch_orders['id']; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $fetch_orders['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h5 class="modal-title" id="orderModalLabel<?php echo $fetch_orders['id']; ?>">Order #<?php echo $fetch_orders['id']; ?> Details</h5>
                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                 <div class="row">
                                    <div class="col-md-6">
                                       <h6 class="fw-bold mb-3">Customer Information</h6>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Customer ID:</div>
                                             <div class="col-7"><?php echo $fetch_orders['user_id']; ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Name:</div>
                                             <div class="col-7"><?php echo htmlspecialchars($fetch_orders['name']); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Email:</div>
                                             <div class="col-7"><?php echo htmlspecialchars($fetch_orders['email']); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Phone:</div>
                                             <div class="col-7"><?php echo htmlspecialchars($fetch_orders['number']); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Address:</div>
                                             <div class="col-7"><?php echo htmlspecialchars($fetch_orders['address']); ?></div>
                                          </div>
                                       </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                       <h6 class="fw-bold mb-3">Order Information</h6>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Order Date:</div>
                                             <div class="col-7"><?php echo date('F d, Y h:i A', strtotime($fetch_orders['placed_on'])); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Payment Method:</div>
                                             <div class="col-7"><?php echo ucfirst($fetch_orders['method']); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Total Products:</div>
                                             <div class="col-7"><?php echo $fetch_orders['total_products']; ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Total Amount:</div>
                                             <div class="col-7 fw-bold">Rs<?php echo number_format($fetch_orders['total_price'], 2); ?></div>
                                          </div>
                                       </div>
                                       <div class="order-detail-row">
                                          <div class="row">
                                             <div class="col-5 text-muted">Payment Status:</div>
                                             <div class="col-7">
                                                <span class="status-badge <?php echo $fetch_orders['payment_status'] == 'completed' ? 'status-completed' : 'status-pending'; ?>">
                                                   <?php echo ucfirst($fetch_orders['payment_status']); ?>
                                                </span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 
                                 <hr>
                                 
                                 <div class="mt-3">
                                    <h6 class="fw-bold mb-3">Products</h6>
                                    <div class="bg-light p-3 rounded">
                                       <?php echo nl2br(htmlspecialchars($fetch_orders['total_products'])); ?>
                                    </div>
                                 </div>
                                 
                                 <hr>
                                 
                                 <form action="" method="post" class="mt-4">
                                    <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                                    <div class="row align-items-end">
                                       <div class="col-md-6">
                                          <label for="update_payment<?php echo $fetch_orders['id']; ?>" class="form-label">Update Payment Status</label>
                                          <select class="form-select" name="update_payment" id="update_payment<?php echo $fetch_orders['id']; ?>">
                                             <option value="" disabled>Select Status</option>
                                             <option value="pending" <?php echo $fetch_orders['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                             <option value="completed" <?php echo $fetch_orders['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                          </select>
                                       </div>
                                       <div class="col-md-6">
                                          <button type="submit" name="update_order" class="btn btn-primary w-100">Update Status</button>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <!-- Delete Confirmation Modal -->
                     <div class="modal fade" id="deleteModal<?php echo $fetch_orders['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $fetch_orders['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h5 class="modal-title" id="deleteModalLabel<?php echo $fetch_orders['id']; ?>">Confirm Deletion</h5>
                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                 <p>Are you sure you want to delete order #<?php echo $fetch_orders['id']; ?>?</p>
                                 <p class="text-danger"><small>This action cannot be undone.</small></p>
                              </div>
                              <div class="modal-footer">
                                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                 <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" class="btn btn-danger">Delete Order</a>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php endwhile; ?>
                  </tbody>
               </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination-container">
               <nav aria-label="Page navigation">
                  <ul class="pagination">
                     <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>" aria-label="Previous">
                           <span aria-hidden="true">&laquo;</span>
                        </a>
                     </li>
                     
                     <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                           <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                              <?php echo $i; ?>
                           </a>
                        </li>
                     <?php endfor; ?>
                     
                     <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>" aria-label="Next">
                           <span aria-hidden="true">&raquo;</span>
                        </a>
                     </li>
                  </ul>
               </nav>
            </div>
            <?php endif; ?>
            
         <?php else: ?>
            <div class="text-center py-5">
               <img src="images/no-data.svg" alt="No Orders" style="width: 120px; opacity: 0.5;" class="mb-3">
               <h5 class="text-muted">No orders found</h5>
               <?php if(!empty($search_query) || $status_filter != 'all'): ?>
                  <p>Try clearing your filters or search query</p>
                  <a href="admin_orders.php" class="btn btn-outline-primary mt-2">Clear Filters</a>
               <?php else: ?>
                  <p>No orders have been placed yet</p>
               <?php endif; ?>
            </div>
         <?php endif; ?>
      </div>
   </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom admin JS file -->
<script src="js/admin_script.js"></script>

</body>
</html>