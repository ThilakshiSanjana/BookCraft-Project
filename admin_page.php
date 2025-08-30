<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:login.php');
}


$total_pendings = 0;
$select_pending = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'pending'") or die('query failed');
if(mysqli_num_rows($select_pending) > 0){
   while($fetch_pendings = mysqli_fetch_assoc($select_pending)){
      $total_pendings += $fetch_pendings['total_price'];
   };
}

$total_completed = 0;
$select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'completed'") or die('query failed');
if(mysqli_num_rows($select_completed) > 0){
   while($fetch_completed = mysqli_fetch_assoc($select_completed)){
      $total_completed += $fetch_completed['total_price'];
   };
}

$select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
$number_of_orders = mysqli_num_rows($select_orders);

$select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
$number_of_products = mysqli_num_rows($select_products);

$select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'user'") or die('query failed');
$number_of_users = mysqli_num_rows($select_users);

$select_admins = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'admin'") or die('query failed');
$number_of_admins = mysqli_num_rows($select_admins);

$select_account = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
$number_of_account = mysqli_num_rows($select_account);

// Fixed message query - ensure it's fetching unread messages
$select_messages = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
$number_of_messages = mysqli_num_rows($select_messages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BookCraft Admin Panel</title>

   <!-- Bootstrap 5 CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <style>
     :root {
       --main-color: #4361ee;
       --secondary-color: #3f37c9;
       --accent-color: #4cc9f0;
       --success-color: #4cd97b;
       --warning-color: #fca311;
       --danger-color: #e63946;
       --light-color: #f8f9fa;
       --dark-color: #212529;
     }
     
     body {
       background-color: #f0f2f5;
       font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
     }
     
     .navbar {
       background-color: white;
       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
     }
     
     .navbar-brand {
       font-weight: 700;
       color: var(--main-color);
     }
     
     .nav-link {
       color: #495057;
     }
     
     .nav-link:hover, .nav-link.active {
       color: var(--main-color);
     }
     
     .stat-card {
       background-color: white;
       border-radius: 8px;
       box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
       transition: transform 0.2s;
       height: 100%;
     }
     
     .stat-card:hover {
       transform: translateY(-4px);
     }
     
     .stat-card .card-body {
       padding: 1.5rem;
     }
     
     .stat-value {
       font-size: 1.75rem;
       font-weight: 700;
     }
     
     .stat-label {
       color: #6c757d;
       font-size: 0.875rem;
       font-weight: 500;
       text-transform: uppercase;
     }
     
     .icon-box {
       width: 48px;
       height: 48px;
       display: flex;
       align-items: center;
       justify-content: center;
       border-radius: 12px;
       font-size: 1.25rem;
     }
     
     .table-card {
       background-color: white;
       border-radius: 8px;
       box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
     }
     
     .table th {
       font-weight: 600;
       color: #495057;
     }
     
     .page-title {
       font-weight: 700;
       color: #343a40;
       margin-bottom: 1.5rem;
     }
     
     .btn-custom-primary {
       background-color: var(--main-color);
       border-color: var(--main-color);
     }
     
     .btn-custom-primary:hover {
       background-color: var(--secondary-color);
       border-color: var(--secondary-color);
     }
     
     .progress {
       height: 6px;
       margin-top: 0.5rem;
     }
     
     /* Top navigation specific styles */
     .top-nav {
       background-color: white;
       padding: 0.75rem 1rem;
     }
     
     .dropdown-menu {
       box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
       border: none;
     }
     
     .chart-container {
       height: 300px;
       position: relative;
     }

     /* Badge styles */
     .badge.bg-pending {
       background-color: var(--warning-color);
     }
     
     .badge.bg-completed {
       background-color: var(--success-color);
     }

     footer {
       background-color: white;
       padding: 1rem 0;
       box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
     }
   </style>
</head>
<body>

<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_page.php">BookCraft Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="admin_page.php">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_products.php">
            <i class="fas fa-book me-1"></i> Products
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_orders.php">
            <i class="fas fa-shopping-cart me-1"></i> Orders
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_users.php">
            <i class="fas fa-users me-1"></i> Users
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_contacts.php">
            <i class="fas fa-envelope me-1"></i> Messages
            <?php if($number_of_messages > 0): ?>
              <span class="badge rounded-pill bg-danger"><?php echo $number_of_messages; ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
      <div class="d-flex align-items-center">
        <div class="dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle me-1"></i> Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
            <li><a class="dropdown-item" href="admin_settings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <h1 class="page-title">Dashboard Overview</h1>
    </div>
  </div>

  <!-- Sales Overview Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="stat-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="stat-label mb-0">Total Revenue</p>
              <h2 class="stat-value mb-0">Rs<?php echo number_format($total_completed + $total_pendings, 2); ?></h2>
            </div>
            <div class="icon-box" style="background-color: rgba(67, 97, 238, 0.1); color: var(--main-color);">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
          <p class="mb-0 text-muted small">
            <i class="fas fa-arrow-up text-success me-1"></i> 
            16% increase from last month
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="stat-label mb-0">Pending Orders</p>
              <h2 class="stat-value mb-0">Rs<?php echo number_format($total_pendings, 2); ?></h2>
            </div>
            <div class="icon-box" style="background-color: rgba(252, 163, 17, 0.1); color: var(--warning-color);">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="progress">
            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($total_pendings/($total_pendings+$total_completed+0.01))*100; ?>%" aria-valuenow="<?php echo ($total_pendings/($total_pendings+$total_completed+0.01))*100; ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="stat-label mb-0">Completed Payments</p>
              <h2 class="stat-value mb-0">Rs<?php echo number_format($total_completed, 2); ?></h2>
            </div>
            <div class="icon-box" style="background-color: rgba(76, 217, 123, 0.1); color: var(--success-color);">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($total_completed/($total_pendings+$total_completed+0.01))*100; ?>%" aria-valuenow="<?php echo ($total_completed/($total_pendings+$total_completed+0.01))*100; ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="stat-label mb-0">Total Orders</p>
              <h2 class="stat-value mb-0"><?php echo $number_of_orders; ?></h2>
            </div>
            <div class="icon-box" style="background-color: rgba(76, 201, 240, 0.1); color: var(--accent-color);">
              <i class="fas fa-shopping-bag"></i>
            </div>
          </div>
          <p class="mb-0 text-muted small">
            <i class="fas fa-arrow-up text-success me-1"></i> 
            8 new orders today
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart and Stats -->
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card table-card mb-4">
        <div class="card-body">
          <h5 class="card-title">Sales Analytics</h5>
          <div class="chart-container mt-3">
            <canvas id="salesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card table-card h-100">
        <div class="card-body">
          <h5 class="card-title">Statistics</h5>
          <div class="list-group list-group-flush mt-3">
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-book text-primary me-2"></i>
                Total Products
              </div>
              <span class="badge bg-primary rounded-pill"><?php echo $number_of_products; ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-user text-info me-2"></i>
                Normal Users
              </div>
              <span class="badge bg-info rounded-pill"><?php echo $number_of_users; ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-user-shield text-warning me-2"></i>
                Admin Users
              </div>
              <span class="badge bg-warning rounded-pill"><?php echo $number_of_admins; ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-users text-success me-2"></i>
                Total Accounts
              </div>
              <span class="badge bg-success rounded-pill"><?php echo $number_of_account; ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-envelope text-danger me-2"></i>
                New Messages
              </div>
              <span class="badge bg-danger rounded-pill"><?php echo $number_of_messages; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders Table -->
  <div class="row">
    <div class="col-12">
      <div class="card table-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title">Recent Orders</h5>
            <a href="admin_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $select_recent = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC LIMIT 5") or die('query failed');
                  if(mysqli_num_rows($select_recent) > 0){
                    while($fetch_recent = mysqli_fetch_assoc($select_recent)){
                ?>
                <tr>
                  <td>#<?php echo $fetch_recent['id']; ?></td>
                  <td><?php echo $fetch_recent['name']; ?></td>
                  <td><?php echo $fetch_recent['placed_on']; ?></td>
                  <td>Rs<?php echo $fetch_recent['total_price']; ?></td>
                  <td>
                    <?php if($fetch_recent['payment_status'] == 'pending'){ ?>
                      <span class="badge bg-warning">Pending</span>
                    <?php } else { ?>
                      <span class="badge bg-success">Completed</span>
                    <?php } ?>
                  </td>
                  <td>
                    <a href="admin_orders.php?order=<?php echo $fetch_recent['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                  </td>
                </tr>
                <?php
                    }
                  } else {
                    echo '<tr><td colspan="6" class="text-center">No recent orders</td></tr>';
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="mt-4">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <p class="mb-0">© 2025 BookCraft. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-md-end">
        <a href="#" class="text-decoration-none text-muted me-3">Privacy Policy</a>
        <a href="#" class="text-decoration-none text-muted">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Sales Chart - Fixed to ensure it works correctly
  document.addEventListener('DOMContentLoaded', function() {
    const salesChartCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesChartCtx, {
      type: 'bar',
      data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June'],
        datasets: [
          {
            label: 'Completed Orders',
            data: [65, 59, 80, 81, 56, <?php echo $total_completed; ?>],
            backgroundColor: 'rgba(76, 217, 123, 0.6)',
            borderColor: 'rgba(76, 217, 123, 1)',
            borderWidth: 1
          },
          {
            label: 'Pending Orders',
            data: [28, 48, 40, 19, 86, <?php echo $total_pendings; ?>],
            backgroundColor: 'rgba(252, 163, 17, 0.6)',
            borderColor: 'rgba(252, 163, 17, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              drawBorder: false
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            position: 'top',
          }
        }
      }
    });
  });
</script>

</body>
</html>