<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

// Initialize message array
$messages = [];

// Add product
if(isset($_POST['add_product'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $description = mysqli_real_escape_string($conn, $_POST['description']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   // Generate unique filename to prevent overwriting
   $image_ext = pathinfo($image, PATHINFO_EXTENSION);
   $new_image_name = uniqid() . '.' . $image_ext;
   $image_folder = 'uploaded_img/'.$new_image_name;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

   if(mysqli_num_rows($select_product_name) > 0){
      $messages[] = ['type' => 'error', 'text' => 'Product name already exists'];
   } else {
      // Check image size before inserting - increased to 5MB
      if($image_size > 5000000){
         $messages[] = ['type' => 'error', 'text' => 'Image size is too large (max 5MB)'];
      } else {
         // Insert with description - we'll add the description column to the database
         $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, description) 
                                                  VALUES('$name', '$price', '$new_image_name', '$description')") 
                                                  or die('query failed');

         if($add_product_query){
            move_uploaded_file($image_tmp_name, $image_folder);
            $messages[] = ['type' => 'success', 'text' => 'Product added successfully!'];
         } else {
            $messages[] = ['type' => 'error', 'text' => 'Product could not be added!'];
         }
      }
   }
}

// Delete product
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   
   if(file_exists('uploaded_img/'.$fetch_delete_image['image'])) {
      unlink('uploaded_img/'.$fetch_delete_image['image']);
   }
   
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $messages[] = ['type' => 'success', 'text' => 'Product deleted successfully!'];
}

// Update product
if(isset($_POST['update_product'])){
   $update_p_id = $_POST['update_p_id'];
   $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   $update_price = $_POST['update_price'];
   $update_description = mysqli_real_escape_string($conn, $_POST['update_description']);

   // Update with description
   mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', description = '$update_description' 
                        WHERE id = '$update_p_id'") or die('query failed');

   $update_image = $_FILES['update_image']['name'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_old_image = $_POST['update_old_image'];

   if(!empty($update_image)){
      if($update_image_size > 5000000){
         $messages[] = ['type' => 'error', 'text' => 'Image file size is too large (max 5MB)'];
      } else {
         // Generate unique filename for update
         $update_image_ext = pathinfo($update_image, PATHINFO_EXTENSION);
         $update_new_image = uniqid() . '.' . $update_image_ext;
         $update_folder = 'uploaded_img/'.$update_new_image;
         
         mysqli_query($conn, "UPDATE `products` SET image = '$update_new_image' WHERE id = '$update_p_id'") or die('query failed');
         move_uploaded_file($update_image_tmp_name, $update_folder);
         
         if(file_exists('uploaded_img/'.$update_old_image)) {
            unlink('uploaded_img/'.$update_old_image);
         }
         
         $messages[] = ['type' => 'success', 'text' => 'Product updated successfully!'];
      }
   } else {
      $messages[] = ['type' => 'success', 'text' => 'Product updated successfully!'];
   }
}

// Get product data via AJAX (no need for file creation)
if(isset($_GET['get_product'])) {
   $product_id = $_GET['get_product'];
   $query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$product_id'") or die('query failed');
   
   if(mysqli_num_rows($query) > 0) {
      $product = mysqli_fetch_assoc($query);
      echo json_encode($product);
      exit();
   } else {
      echo json_encode(["error" => "Product not found"]);
      exit();
   }
}

// Count total products
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `products`"));

// Calculate total product value
$total_value_query = mysqli_query($conn, "SELECT SUM(price) as total_value FROM `products`");
$total_value = mysqli_fetch_assoc($total_value_query)['total_value'] ?? 0;

// Get categories for dropdown
$categories = [
   'Featured' => [
      'Resale book',  'stationery item', 'Latest book ', 'Trande Book', 
      
   ],
   'Fiction' => [
       'Science Fiction',  'Mystery', 'Thriller', 
      'Horror', 'Historical Fiction', 'fantacy'
   ],
   
   'Non-Fiction' => [
      'Biography', 'Autobiography', 'Memoir', 'Self-Help', 'Business', 
      'History', 'Science', 'Philosophy', 'Travel'
   ],
   'Academic' => [
      'Textbook', 'Reference', 'Research', 'Academic Journal'
   ],
   'Children' => [
      'Picture Book', 'Middle Grade', 'Educational'
   ],
   'Other' => [
      'Comics', 'Manga', 'Poetry', 'Anthology', 'Miscellaneous'
   ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BookCraft - Product Management</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- Custom admin CSS file -->
   <link rel="stylesheet" href="css/admin_style.css">
   
   <!-- Additional custom styles -->
   <style>
      :root {
         --primary: #6C63FF;
         --primary-dark: #5A54D6;
         --secondary: #8E8AFF;
         --danger: #FF6B6B;
         --success: #28C76F;
         --warning: #FFC107;
         --info: #00CFE8;
         --light: #F8F8FB;
         --dark: #2C3E50;
         --gray: #A7A7A7;
         --white: #FFFFFF;
         --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
         --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.15);
         --border-radius: 10px;
         --transition: all 0.3s ease;
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         transition: var(--transition);
      }

      body {
         background-color: #F8F8FB;
      }

      /* Main Container */
      .dashboard-container {
         padding: 2rem;
         max-width: 1400px;
         margin: 0 auto;
      }

      /* Dashboard Header */
      .dashboard-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 2rem;
         padding-bottom: 1rem;
         border-bottom: 1px solid #EAEAEA;
      }

      .dashboard-title {
         color: var(--dark);
         font-size: 1.8rem;
         font-weight: 600;
      }

      /* Stats Section */
      .stats-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 1.5rem;
         margin-bottom: 2rem;
      }

      .stat-card {
         background: var(--white);
         padding: 1.5rem;
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         display: flex;
         align-items: center;
         gap: 1.2rem;
         transition: var(--transition);
      }

      .stat-card:hover {
         transform: translateY(-5px);
         box-shadow: var(--shadow-hover);
      }

      .stat-icon {
         background: var(--primary);
         color: white;
         width: 60px;
         height: 60px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.5rem;
      }

      .stat-icon.secondary {
         background: var(--secondary);
      }

      .stat-info h3 {
         font-size: 1.8rem;
         color: var(--dark);
         margin-bottom: 0.3rem;
      }

      .stat-info p {
         color: var(--gray);
         font-size: 0.9rem;
      }

      /* Notification System */
      .notification-container {
         position: fixed;
         top: 20px;
         right: 20px;
         z-index: 1000;
         display: flex;
         flex-direction: column;
         gap: 10px;
         max-width: 350px;
      }

      .notification {
         padding: 1rem 1.5rem;
         border-radius: var(--border-radius);
         color: white;
         box-shadow: var(--shadow);
         display: flex;
         align-items: center;
         gap: 0.8rem;
         animation: slideIn 0.4s ease, fadeOut 0.4s 4s forwards;
         opacity: 0;
         transform: translateX(100%);
      }

      .notification.success {
         background-color: var(--success);
      }

      .notification.error {
         background-color: var(--danger);
      }

      .notification-content {
         flex: 1;
      }

      .notification-close {
         background: transparent;
         border: none;
         color: white;
         font-size: 1.2rem;
         cursor: pointer;
         opacity: 0.7;
      }

      .notification-close:hover {
         opacity: 1;
      }

      @keyframes slideIn {
         to { opacity: 1; transform: translateX(0); }
      }

      @keyframes fadeOut {
         to { opacity: 0; transform: translateX(100%); }
      }

      /* Main Content Grid */
      .content-grid {
         display: grid;
         grid-template-columns: 350px 1fr;
         gap: 2rem;
      }

      /* Add Product Form */
      .add-product-card {
         background: var(--white);
         padding: 2rem;
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         position: sticky;
         top: 2rem;
         height: fit-content;
      }

      .add-product-card h2 {
         color: var(--dark);
         margin-bottom: 1.8rem;
         font-size: 1.4rem;
         position: relative;
         padding-bottom: 0.8rem;
      }

      .add-product-card h2:after {
         content: '';
         position: absolute;
         left: 0;
         bottom: 0;
         width: 50px;
         height: 3px;
         background: var(--primary);
      }

      .form-group {
         margin-bottom: 1.5rem;
      }

      .form-group label {
         display: block;
         margin-bottom: 0.5rem;
         color: var(--dark);
         font-weight: 500;
         font-size: 0.95rem;
      }

      .form-control {
         width: 100%;
         padding: 0.9rem 1.2rem;
         border: 1px solid #E2E8F0;
         border-radius: var(--border-radius);
         background: var(--light);
         color: var(--dark);
         font-size: 0.95rem;
      }

      .form-control:focus {
         border-color: var(--primary);
         outline: none;
         box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
      }

      /* Select with optgroup styling */
      select.form-control optgroup {
         font-weight: 600;
         color: var(--primary-dark);
         font-size: 0.9rem;
      }

      select.form-control option {
         font-weight: normal;
         color: var(--dark);
         padding: 8px;
      }

      /* Textarea styling */
      textarea.form-control {
         min-height: 120px;
         resize: vertical;
      }

      /* File Input Styling */
      .file-input-container {
         position: relative;
         border: 2px dashed #E2E8F0;
         border-radius: var(--border-radius);
         padding: 2rem 1.5rem;
         text-align: center;
         margin-bottom: 1rem;
         cursor: pointer;
         transition: all 0.3s;
      }

      .file-input-container:hover {
         border-color: var(--primary);
      }

      .file-input-container i {
         font-size: 2.5rem;
         color: var(--primary);
         margin-bottom: 1rem;
      }

      .file-input-container p {
         color: var(--gray);
         margin-bottom: 0.5rem;
      }

      .file-input {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         opacity: 0;
         cursor: pointer;
      }

      .file-name {
         font-size: 0.9rem;
         color: var(--primary);
         margin-top: 0.5rem;
      }

      .image-preview {
         width: 100%;
         height: 180px;
         border-radius: var(--border-radius);
         background-color: #f1f1f1;
         margin-top: 1rem;
         overflow: hidden;
         display: none;
      }

      .image-preview img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      /* Button Styles */
      .btn {
         padding: 0.9rem 1.5rem;
         border: none;
         border-radius: var(--border-radius);
         cursor: pointer;
         font-weight: 500;
         text-transform: uppercase;
         letter-spacing: 0.5px;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         gap: 0.5rem;
         transition: all 0.3s;
         font-size: 0.9rem;
      }

      .btn-primary {
         background: var(--primary);
         color: white;
      }

      .btn-primary:hover {
         background: var(--primary-dark);
         box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
      }

      .btn-danger {
         background: var(--danger);
         color: white;
      }

      .btn-danger:hover {
         background: #e84a5f;
         box-shadow: 0 4px 12px rgba(235, 77, 75, 0.3);
      }

      .btn-warning {
         background: var(--warning);
         color: white;
      }

      .btn-warning:hover {
         background: #e5ac00;
         box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
      }

      .btn-block {
         width: 100%;
      }

      /* Products Section */
      .products-section {
         display: flex;
         flex-direction: column;
      }

      /* Search and Filters */
      .toolbar {
         margin-bottom: 1.5rem;
         display: flex;
         flex-wrap: wrap;
         gap: 1rem;
         align-items: center;
      }

      .search-container {
         position: relative;
         flex: 1;
         min-width: 200px;
      }

      .search-input {
         width: 100%;
         padding: 0.9rem 1.2rem;
         padding-left: 3rem;
         border: 1px solid #E2E8F0;
         border-radius: var(--border-radius);
         background: var(--white);
         color: var(--dark);
      }

      .search-icon {
         position: absolute;
         top: 50%;
         left: 1rem;
         transform: translateY(-50%);
         color: var(--gray);
      }

      .sort-select {
         padding: 0.9rem 1.2rem;
         border: 1px solid #E2E8F0;
         border-radius: var(--border-radius);
         background: var(--white);
         color: var(--dark);
         min-width: 180px;
      }

      /* Products Grid */
      .products-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 1.5rem;
      }

      .product-card {
         background: var(--white);
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         overflow: hidden;
         transition: transform 0.3s, box-shadow 0.3s;
         display: flex;
         flex-direction: column;
      }

      .product-card:hover {
         transform: translateY(-5px);
         box-shadow: var(--shadow-hover);
      }

      .product-image-container {
         position: relative;
         height: 220px;
         overflow: hidden;
         border-bottom: 1px solid #f1f1f1;
      }

      .product-image {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.5s;
      }

      .product-card:hover .product-image {
         transform: scale(1.05);
      }

      .product-category {
         position: absolute;
         top: 10px;
         right: 10px;
         background: rgba(108, 99, 255, 0.8);
         color: white;
         padding: 0.3rem 0.8rem;
         border-radius: 20px;
         font-size: 0.75rem;
         font-weight: 500;
         z-index: 1;
      }

      .product-details {
         padding: 1.5rem;
         flex-grow: 1;
         display: flex;
         flex-direction: column;
      }

      .product-name {
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 0.8rem;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
         text-overflow: ellipsis;
         min-height: 3rem;
      }

      .product-price {
         color: var(--primary);
         font-weight: 700;
         font-size: 1.4rem;
         margin-bottom: 0.8rem;
      }

      .product-description {
         color: var(--gray);
         font-size: 0.9rem;
         margin-bottom: 1.2rem;
         display: -webkit-box;
         -webkit-line-clamp: 3;
         -webkit-box-orient: vertical;
         overflow: hidden;
         flex-grow: 1;
      }

      .product-actions {
         display: flex;
         gap: 0.8rem;
         margin-top: auto;
      }

      .action-btn {
         flex: 1;
         padding: 0.7rem;
         border: none;
         border-radius: var(--border-radius);
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 0.5rem;
         font-size: 0.9rem;
         font-weight: 500;
         transition: all 0.3s;
      }

      .edit-btn {
         background: var(--warning);
         color: white;
      }

      .edit-btn:hover {
         background: #e5ac00;
         transform: translateY(-2px);
      }

      .delete-btn {
         background: var(--danger);
         color: white;
      }

      .delete-btn:hover {
         background: #e84a5f;
         transform: translateY(-2px);
      }

      /* Empty State */
      .empty-message {
         grid-column: 1 / -1;
         text-align: center;
         padding: 3rem;
         background: var(--white);
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         color: var(--gray);
      }

      .empty-message i {
         font-size: 4rem;
         margin-bottom: 1.5rem;
         display: block;
         color: var(--primary);
         opacity: 0.7;
      }

      .empty-message h3 {
         font-size: 1.5rem;
         color: var(--dark);
         margin-bottom: 1rem;
      }

      .empty-message p {
         margin-bottom: 0.5rem;
      }

      /* Modal Styles */
      .modal-backdrop {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0, 0, 0, 0.5);
         backdrop-filter: blur(3px);
         display: flex;
         align-items: center;
         justify-content: center;
         z-index: 1000;
         opacity: 0;
         pointer-events: none;
         transition: opacity 0.3s;
      }

      .modal-backdrop.active {
         opacity: 1;
         pointer-events: all;
      }

      .modal {
         background: var(--white);
         border-radius: var(--border-radius);
         width: 90%;
         max-width: 650px;
         max-height: 90vh;
         overflow-y: auto;
         box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
         transform: translateY(-30px) scale(0.95);
         transition: transform 0.3s;
      }

      .modal-backdrop.active .modal {
         transform: translateY(0) scale(1);
      }

      .modal-header {
         padding: 1.5rem 2rem;
         border-bottom: 1px solid #eee;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .modal-title {
         font-size: 1.3rem;
         color: var(--dark);
         font-weight: 600;
      }

      .modal-close {
         background: none;
         border: none;
         font-size: 1.5rem;
         cursor: pointer;
         color: var(--gray);
         transition: color 0.3s;
      }

      .modal-close:hover {
         color: var(--danger);
      }

      .modal-body {
         padding: 2rem;
      }

      .modal-footer {
         padding: 1.2rem 2rem;
         border-top: 1px solid #eee;
         display: flex;
         justify-content: flex-end;
         gap: 1rem;
      }

      /* Current image preview in update form */
      .current-image-preview {
         width: 120px;
         height: 120px;
         object-fit: cover;
         border-radius: var(--border-radius);
         margin-bottom: 1rem;
         border: 1px solid #eee;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      /* Pagination */
      .pagination {
         display: flex;
         justify-content: center;
         align-items: center;
         margin-top: 2rem;
         gap: 0.5rem;
      }

      .pagination-btn {
         background: var(--white);
         border: 1px solid #E2E8F0;
         color: var(--dark);
         width: 40px;
         height: 40px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: 500;
         transition: all 0.3s;
      }

      .pagination-btn:hover, .pagination-btn.active {
         background: var(--primary);
         color: white;
         border-color: var(--primary);
      }

      .pagination-btn.disabled {
         opacity: 0.5;
         cursor: not-allowed;
      }

      /* Loader */
      .loader {
         display: inline-block;
         width: 30px;
         height: 30px;
         border: 3px solid rgba(108, 99, 255, 0.3);
         border-radius: 50%;
         border-top-color: var(--primary);
         animation: spin 1s ease-in-out infinite;
      }

      @keyframes spin {
         to { transform: rotate(360deg); }
      }

      /* Loading overlay */
      .loading-overlay {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(255, 255, 255, 0.8);
         display: flex;
         align-items: center;
         justify-content: center;
         z-index: 2000;
         opacity: 0;
         pointer-events: none;
         transition: opacity 0.3s;
      }

      .loading-overlay.active {
         opacity: 1;
         pointer-events: all;
      }

      /* Responsive */
      @media (max-width: 1200px) {
         .content-grid {
            grid-template-columns: 300px 1fr;
         }
      }

      @media (max-width: 992px) {
         .content-grid {
            grid-template-columns: 1fr;
         }
         
         .add-product-card {
            position: static;
            margin-bottom: 2rem;
         }
      }

      @media (max-width: 768px) {
         .dashboard-container {
            padding: 1rem;
         }
         
         .toolbar {
            flex-direction: column;
            align-items: stretch;
         }
         
         .products-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
         }
      }

      @media (max-width: 576px) {
         .products-container {
            grid-template-columns: 1fr;
         }
         
         .product-card {
            max-width: 100%;
         }
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<div class="dashboard-container">
   <div class="dashboard-header">
      <h1 class="dashboard-title">Product Management</h1>
   </div>

   <!-- Stats Section -->
   <div class="stats-container">
      <div class="stat-card">
         <div class="stat-icon">
            <i class="fas fa-book"></i>
         </div>
         <div class="stat-info">
            <h3><?php echo $total_products; ?></h3>
            <p>Total Products</p>
         </div>
      </div>
      
      <div class="stat-card">
         <div class="stat-icon secondary">
            <i class="fas fa-dollar-sign"></i>
         </div>
         <div class="stat-info">
            <h3>Rs<?php echo number_format($total_value, 2); ?></h3>
            <p>Total Inventory Value</p>
         </div>
      </div>
   </div>

   <!-- Notification Container -->
   <div class="notification-container" id="notification-container"></div>

   <div class="content-grid">
      <!-- Add Product Form -->
      <div class="add-product-card">
         <h2>Add New Product</h2>
         <form action="" method="post" enctype="multipart/form-data" id="add-product-form">
            <div class="form-group">
               <label for="name">Product Name</label>
               <input type="text" id="name" name="name" class="form-control" placeholder="Enter product name" required>
            </div>
            
            <div class="form-group">
               <label for="price">Price (Rs)</label>
               <input type="number" id="price" name="price" min="0" step="0.01" class="form-control" placeholder="Enter product price" required>
            </div>

            <div class="form-group">
               <label for="description">Book Description</label>
               <textarea id="description" name="description" class="form-control" placeholder="Enter book description" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
               <label>Product Image</label>
               <div class="file-input-container">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Click to upload image</p>
                  <input type="file" name="image" id="product-image" class="file-input" accept="image/jpg, image/jpeg, image/png" required>
                  <div class="file-name" id="file-name-display"></div>
               </div>
               <small>Max file size: 5MB. Formats: JPG, JPEG, PNG</small>
               
               <!-- Image preview -->
               <div class="image-preview" id="image-preview">
                  <img src="" alt="Image Preview" id="preview-img">
               </div>
            </div>
            
            <button type="submit" name="add_product" class="btn btn-primary btn-block">
               <i class="fas fa-plus"></i> Add Product
            </button>
         </form>
      </div>

      <!-- Products Display -->
      <div class="products-section">
         <div class="toolbar">
            <div class="search-container">
               <i class="fas fa-search search-icon"></i>
               <input type="text" id="search-products" class="search-input" placeholder="Search products...">
            </div>
            
            <select id="sort-products" class="sort-select">
               <option value="newest">Newest First</option>
               <option value="oldest">Oldest First</option>
               <option value="name-asc">Name (A-Z)</option>
               <option value="name-desc">Name (Z-A)</option>
               <option value="price-asc">Price (Low to High)</option>
               <option value="price-desc">Price (High to Low)</option>
            </select>
         </div>

         <div class="products-container" id="products-container">
            <?php
            $select_products = mysqli_query($conn, "SELECT * FROM `products` ORDER BY id DESC") or die('query failed');
            if(mysqli_num_rows($select_products) > 0){
               while($fetch_products = mysqli_fetch_assoc($select_products)){
            ?>
            <div class="product-card" 
                 data-product-name="<?php echo strtolower($fetch_products['name']); ?>"
                 data-product-price="<?php echo $fetch_products['price']; ?>"
                 data-product-id="<?php echo $fetch_products['id']; ?>">
               <div class="product-image-container">
                  <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo $fetch_products['name']; ?>" class="product-image">
               </div>
               <div class="product-details">
                  <div class="product-name"><?php echo $fetch_products['name']; ?></div>
                  <div class="product-price">Rs<?php echo number_format($fetch_products['price'], 2); ?></div>
                  <?php if(isset($fetch_products['description']) && !empty($fetch_products['description'])): ?>
                  <div class="product-description"><?php echo $fetch_products['description']; ?></div>
                  <?php endif; ?>
                  <div class="product-actions">
                     <button class="action-btn edit-btn" onclick="editProduct(<?php echo $fetch_products['id']; ?>)">
                        <i class="fas fa-edit"></i> Edit
                     </button>
                     <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $fetch_products['id']; ?>)">
                        <i class="fas fa-trash-alt"></i> Delete
                     </button>
                  </div>
               </div>
            </div>
            <?php
               }
            } else {
            ?>
               <div class="empty-message">
                  <i class="fas fa-box-open"></i>
                  <h3>No products found</h3>
                  <p>Add your first product using the form on the left.</p>
                  <p>Your products will appear here once added.</p>
               </div>
            <?php
            }
            ?>
         </div>
         
         <!-- Pagination (will be dynamically populated if needed) -->
         <div class="pagination" id="pagination"></div>
      </div>
   </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-backdrop" id="edit-modal">
   <div class="modal">
      <div class="modal-header">
         <h3 class="modal-title">Edit Product</h3>
         <button class="modal-close" id="close-modal">&times;</button>
      </div>
      <div class="modal-body">
         <form action="" method="post" enctype="multipart/form-data" id="edit-form">
            <input type="hidden" name="update_p_id" id="update_p_id">
            <input type="hidden" name="update_old_image" id="update_old_image">
            
            <div class="form-group">
               <label>Current Image</label>
               <img src="" alt="Current Product Image" id="current_image_preview" class="current-image-preview">
            </div>
            
            <div class="form-group">
               <label for="update_name">Product Name</label>
               <input type="text" name="update_name" id="update_name" class="form-control" required>
            </div>
            
            <div class="form-group">
               <label for="update_price">Price (Rs)</label>
               <input type="number" name="update_price" id="update_price" min="0" step="0.01" class="form-control" required>
            </div>

            <div class="form-group">
               <label for="update_description">Book Description</label>
               <textarea id="update_description" name="update_description" class="form-control" placeholder="Enter book description" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
               <label>Change Image (Optional)</label>
               <div class="file-input-container">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Click to upload new image</p>
                  <input type="file" name="update_image" id="update_image" class="file-input" accept="image/jpg, image/jpeg, image/png">
                  <div class="file-name" id="update-file-name-display"></div>
               </div>
               <small>Max file size: 5MB. Formats: JPG, JPEG, PNG</small>
               
               <!-- Update image preview -->
               <div class="image-preview" id="update-image-preview">
                  <img src="" alt="Image Preview" id="update-preview-img">
               </div>
            </div>
            
            <div class="modal-footer">
               <button type="button" class="btn btn-danger" id="cancel-edit">Cancel</button>
               <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loading-overlay">
   <div class="loader"></div>
</div>

<script>
   // Show notifications from PHP
   <?php
   if(!empty($messages)) {
      foreach($messages as $idx => $msg) {
         echo "setTimeout(() => { showNotification('{$msg['text']}', '{$msg['type']}'); }, " . ($idx * 300) . ");";
      }
   }
   ?>

   // Notification system
   function showNotification(message, type = 'success') {
      const container = document.getElementById('notification-container');
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      
      const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
      
      notification.innerHTML = `
         <i class="fas ${icon}"></i>
         <div class="notification-content">${message}</div>
         <button class="notification-close">&times;</button>
      `;
      
      container.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
         notification.style.opacity = '1';
         notification.style.transform = 'translateX(0)';
      }, 10);
      
      // Auto remove after 5 seconds
      const timeout = setTimeout(() => {
         removeNotification(notification);
      }, 5000);
      
      // Close button
      const closeBtn = notification.querySelector('.notification-close');
      closeBtn.addEventListener('click', () => {
         clearTimeout(timeout);
         removeNotification(notification);
      });
   }
   
   function removeNotification(notification) {
      notification.style.opacity = '0';
      notification.style.transform = 'translateX(100%)';
      
      setTimeout(() => {
         notification.remove();
      }, 300);
   }

   // File input preview for add product
   document.getElementById('product-image').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
         const fileName = file.name;
         document.getElementById('file-name-display').textContent = fileName;
         
         // Show image preview
         const reader = new FileReader();
         reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
         }
         reader.readAsDataURL(file);
      } else {
         document.getElementById('file-name-display').textContent = 'No file selected';
         document.getElementById('image-preview').style.display = 'none';
      }
   });

   // File input preview for update product
   document.getElementById('update_image').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
         const fileName = file.name;
         document.getElementById('update-file-name-display').textContent = fileName;
         
         // Show image preview
         const reader = new FileReader();
         reader.onload = function(e) {
            document.getElementById('update-preview-img').src = e.target.result;
            document.getElementById('update-image-preview').style.display = 'block';
         }
         reader.readAsDataURL(file);
      } else {
         document.getElementById('update-file-name-display').textContent = 'No file selected';
         document.getElementById('update-image-preview').style.display = 'none';
      }
   });

   // Search functionality
   document.getElementById('search-products').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      filterProducts();
   });

   // Sort functionality
   document.getElementById('sort-products').addEventListener('change', function(e) {
      filterProducts();
   });

   function filterProducts() {
      const searchTerm = document.getElementById('search-products').value.toLowerCase();
      const sortValue = document.getElementById('sort-products').value;
      const productCards = document.querySelectorAll('.product-card');
      const productsContainer = document.getElementById('products-container');
      
      // First filter by search term
      let visibleProducts = Array.from(productCards).filter(card => {
         const productName = card.getAttribute('data-product-name');
         return productName.includes(searchTerm);
      });
      
      // Then sort
      visibleProducts.sort((a, b) => {
         const nameA = a.getAttribute('data-product-name');
         const nameB = b.getAttribute('data-product-name');
         const priceA = parseFloat(a.getAttribute('data-product-price'));
         const priceB = parseFloat(b.getAttribute('data-product-price'));
         const idA = parseInt(a.getAttribute('data-product-id'));
         const idB = parseInt(b.getAttribute('data-product-id'));
         
         switch(sortValue) {
            case 'newest':
               return idB - idA;
            case 'oldest':
               return idA - idB;
            case 'name-asc':
               return nameA.localeCompare(nameB);
            case 'name-desc':
               return nameB.localeCompare(nameA);
            case 'price-asc':
               return priceA - priceB;
            case 'price-desc':
               return priceB - priceA;
            default:
               return 0;
         }
      });
      
      // Hide all products first
      productCards.forEach(card => {
         card.style.display = 'none';
      });
      
      // Show filtered and sorted products
      visibleProducts.forEach(card => {
         card.style.display = 'block';
      });
      
      // Show empty message if no products match
      const emptyMessage = document.querySelector('.empty-message');
      if (emptyMessage) emptyMessage.remove();
      
      if (visibleProducts.length === 0) {
         const noResults = document.createElement('div');
         noResults.className = 'empty-message';
         noResults.innerHTML = `
            <i class="fas fa-search"></i>
            <h3>No products found</h3>
            <p>No products match your search criteria.</p>
            <p>Try different search terms or clear the search.</p>
         `;
         productsContainer.appendChild(noResults);
      }
   }

   // Edit product functionality
   function editProduct(id) {
      // Show loading
      document.getElementById('loading-overlay').classList.add('active');
      
      // Fetch product data with AJAX
      fetch(`admin_products.php?get_product=${id}`)
         .then(response => response.json())
         .then(data => {
            document.getElementById('update_p_id').value = data.id;
            document.getElementById('update_name').value = data.name;
            document.getElementById('update_price').value = data.price;
            document.getElementById('update_description').value = data.description || '';
            document.getElementById('update_old_image').value = data.image;
            document.getElementById('current_image_preview').src = `uploaded_img/${data.image}`;
            
            // Hide loading and show modal
            document.getElementById('loading-overlay').classList.remove('active');
            document.getElementById('edit-modal').classList.add('active');
         })
         .catch(error => {
            console.error('Error fetching product data:', error);
            document.getElementById('loading-overlay').classList.remove('active');
            showNotification('Failed to load product data. Please try again.', 'error');
         });
   }

   // Close modal functions
   function closeEditModal() {
      document.getElementById('edit-modal').classList.remove('active');
      document.getElementById('update-image-preview').style.display = 'none';
      document.getElementById('update-file-name-display').textContent = '';
   }

   document.getElementById('close-modal').addEventListener('click', closeEditModal);
   document.getElementById('cancel-edit').addEventListener('click', closeEditModal);

   // Close modal when clicking outside
   document.getElementById('edit-modal').addEventListener('click', function(e) {
      if (e.target === this) {
         closeEditModal();
      }
   });

   // Delete confirmation
   function confirmDelete(id) {
      if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
         window.location.href = `admin_products.php?delete=${id}`;
      }
   }

   // Form validation and loading state
   document.getElementById('add-product-form').addEventListener('submit', function(e) {
      const fileInput = document.getElementById('product-image');
      if (fileInput.files.length > 0) {
         const fileSize = fileInput.files[0].size;
         if (fileSize > 5000000) { // 5MB
            e.preventDefault();
            showNotification('Image size is too large (max 5MB)', 'error');
            return;
         }
      }
      
      document.getElementById('loading-overlay').classList.add('active');
   });

   document.getElementById('edit-form').addEventListener('submit', function(e) {
      const fileInput = document.getElementById('update_image');
      if (fileInput.files.length > 0) {
         const fileSize = fileInput.files[0].size;
         if (fileSize > 5000000) { // 5MB
            e.preventDefault();
            showNotification('Image size is too large (max 5MB)', 'error');
            return;
         }
      }
      
      document.getElementById('loading-overlay').classList.add('active');
   });
</script>

</body>
</html>