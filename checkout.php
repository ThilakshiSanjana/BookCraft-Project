<?php

include 'config.php';
session_start();


$message = [];

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['order_btn'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = $_POST['number'];
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $address = mysqli_real_escape_string($conn, 'flat no. '. $_POST['flat'].', '. $_POST['street'].', '. $_POST['city'].', '. $_POST['state'].', '. $_POST['country'].' - '. $_POST['pin_code']);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
  
   $cart_products = [];

   $cart_query = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'") or die('query failed');
   if(mysqli_num_rows($cart_query) > 0){
      while($cart_item = mysqli_fetch_assoc($cart_query)){
         $cart_products[] = $cart_item['name'].' ('.$cart_item['quantity'].') ';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ',$cart_products);

   $order_query = mysqli_query($conn, "SELECT * FROM orders WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   }else{
      if(mysqli_num_rows($order_query) > 0){
         $message[] = 'order already placed!'; 
      }else{
         mysqli_query($conn, "INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');
         $message[] = 'order placed successfully!';
         mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('query failed');
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout | BookCraft</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      :root {
         --primary: #6c5ce7;
         --secondary: #a29bfe;
         --accent: #fd79a8;
         --light: #f8f9fa;
         --dark: #343a40;
         --success: #00b894;
         --danger: #d63031;
         --warning: #fdcb6e;
         --gray: #718093;
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         transition: all 0.3s ease;
      }
      
      body {
         background-color: #f5f7fa;
      }
      
      .checkout-container {
         max-width: 1200px;
         margin: 2rem auto;
         padding: 0 1.5rem;
      }
      
      .checkout-header {
         text-align: center;
         padding: 1.5rem 0;
         margin-bottom: 2rem;
         background-color: white;
         border-radius: 8px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }
      
      .checkout-header h2 {
         font-size: 2rem;
         color: var(--dark);
         margin-bottom: 0.5rem;
      }
      
      .checkout-header .breadcrumb {
         display: flex;
         justify-content: center;
         gap: 0.5rem;
         color: var(--gray);
      }
      
      .checkout-header .breadcrumb a {
         color: var(--primary);
         text-decoration: none;
         height: 1.5rem;
      }
      
      .checkout-header .breadcrumb a:hover {
         text-decoration: underline;
      }
      
      .checkout-progress {
         display: flex;
         justify-content: space-between;
         max-width: 600px;
         margin: 0 auto 2rem;
      }
      
      .progress-step {
         display: flex;
         flex-direction: column;
         align-items: center;
         position: relative;
         z-index: 1;
      }
      
      .progress-step::before {
         content: '';
         position: absolute;
         width: 30px;
         height: 30px;
         background-color: var(--primary);
         border-radius: 50%;
         z-index: -1;
      }
      
      .progress-step.active::before {
         background-color: var(--primary);
      }
      
      .progress-step.completed::before {
         background-color: var(--success);
      }
      
      .progress-step .step-number {
         width: 30px;
         height: 30px;
         background-color: white;
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         font-weight: bold;
         color: var(--primary);
         margin-bottom: 0.5rem;
      }
      
      .progress-step.active .step-number {
         color: white;
         background-color: var(--primary);
      }
      
      .progress-step.completed .step-number {
         color: white;
         background-color: var(--success);
      }
      
      .progress-step .step-label {
         font-size: 0.9rem;
         color: var(--gray);
      }
      
      .progress-step.active .step-label {
         color: var(--dark);
         font-weight: 500;
      }
      
      .cart-summary {
         background-color: white;
         border-radius: 8px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
         padding: 1.5rem;
         margin-bottom: 2rem;
      }
      
      .cart-summary h3 {
         color: var(--dark);
         margin-bottom: 1.5rem;
         font-size: 1.5rem;
         padding-bottom: 0.5rem;
         border-bottom: 1px solid #eee;
      }
      
      .cart-item {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 0.75rem 0;
         border-bottom: 1px solid #f1f1f1;
      }
      
      .cart-item:last-child {
         border-bottom: none;
      }
      
      .cart-item .item-name {
         font-weight: 500;
      }
      
      .cart-item .item-price {
         color: var(--gray);
      }
      
      .cart-total {
         display: flex;
         justify-content: space-between;
         padding: 1rem 0;
         margin-top: 1rem;
         border-top: 2px solid #eee;
         font-weight: bold;
         font-size: 1.2rem;
      }
      
      .empty-cart {
         text-align: center;
         padding: 2rem;
         color: var(--gray);
         font-size: 1.1rem;
      }
      
      .checkout-form {
         background-color: white;
         border-radius: 8px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
         padding: 1.5rem;
      }
      
      .checkout-form h3 {
         color: var(--dark);
         margin-bottom: 1.5rem;
         font-size: 1.5rem;
         padding-bottom: 0.5rem;
         border-bottom: 1px solid #eee;
      }
      
      .form-section {
         margin-bottom: 1.5rem;
      }
      
      .form-section h4 {
         margin-bottom: 1rem;
         color: var(--dark);
         font-size: 1.1rem;
      }
      
      .form-row {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 1rem;
         margin-bottom: 1rem;
      }
      
      .form-group {
         margin-bottom: 1rem;
      }
      
      .form-group label {
         display: block;
         margin-bottom: 0.5rem;
         font-weight: 500;
         color: var(--dark);
      }
      
      .form-control {
         width: 100%;
         padding: 0.75rem;
         border: 1px solid #ddd;
         border-radius: 4px;
         font-size: 1rem;
         transition: border-color 0.3s;
      }
      
      .form-control:focus {
         border-color: var(--primary);
         outline: none;
      }
      
      .payment-methods {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 1rem;
         margin-top: 1rem;
      }
      
      .payment-method {
         border: 1px solid #ddd;
         border-radius: 4px;
         padding: 1rem;
         cursor: pointer;
         text-align: center;
         transition: all 0.3s;
      }
      
      .payment-method:hover {
         border-color: var(--primary);
      }
      
      .payment-method.selected {
         border-color: var(--primary);
         background-color: rgba(108, 92, 231, 0.1);
      }
      
      .payment-method i {
         font-size: 1.5rem;
         margin-bottom: 0.5rem;
         color: var(--primary);
      }
      
      .btn-primary {
         background-color: var(--primary);
         color: white;
         border: none;
         padding: 0.75rem 1.5rem;
         border-radius: 4px;
         font-size: 1rem;
         font-weight: 500;
         cursor: pointer;
         transition: background-color 0.3s;
         width: 100%;
         margin-top: 1rem;
      }
      
      .btn-primary:hover {
         background-color: #5b4bc4;
      }
      
      .alert {
         padding: 0.75rem 1.25rem;
         margin-bottom: 1rem;
         border-radius: 4px;
      }
      
      .alert-success {
         background-color: rgba(0, 184, 148, 0.1);
         color: var(--success);
         border: 1px solid rgba(0, 184, 148, 0.2);
      }
      
      .alert-danger {
         background-color: rgba(214, 48, 49, 0.1);
         color: var(--danger);
         border: 1px solid rgba(214, 48, 49, 0.2);
      }
      
      @media (max-width: 768px) {
         .form-row {
            grid-template-columns: 1fr;
         }
         
         .payment-methods {
            grid-template-columns: 1fr;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="checkout-container">
   <div class="checkout-header">
      <h2>Checkout</h2>
      <div class="breadcrumb">
         <a href="home.php">Home</a> 
         <span>/</span> 
         <a href="cart.php">Cart</a> 
         <span>/</span> 
         <span>Checkout</span>
      </div>
   </div>
   
   <div class="checkout-progress">
      <div class="progress-step completed">
         <div class="step-number">1</div>
         <div class="step-label">Cart</div>
      </div>
      <div class="progress-step active">
         <div class="step-number">2</div>
         <div class="step-label">Checkout</div>
      </div>
      <div class="progress-step">
         <div class="step-number">3</div>
         <div class="step-label">Confirmation</div>
      </div>
   </div>
   
   <?php
   // Fixed foreach() error by making sure $message is an array
   if(isset($message) && is_array($message)){
      foreach($message as $msg){
         echo '<div class="alert alert-success">'.$msg.'</div>';
      }
   }
   ?>
   
   <div class="cart-summary">
      <h3>Order Summary</h3>
      
      <?php  
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'") or die('query failed');
      if(mysqli_num_rows($select_cart) > 0){
         while($fetch_cart = mysqli_fetch_assoc($select_cart)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
      ?>
      
      <div class="cart-item">
         <div class="item-name"><?php echo $fetch_cart['name']; ?></div>
         <div class="item-price">Rs<?php echo $fetch_cart['price']; ?> × <?php echo $fetch_cart['quantity']; ?></div>
      </div>
      
      <?php
         }
      } else {
         echo '<div class="empty-cart">Your cart is empty</div>';
      }
      ?>
      
      <div class="cart-total">
         <span>Total Amount:</span>
         <span>Rs<?php echo $grand_total; ?></span>
      </div>
   </div>
   
   <form action="" method="post" class="checkout-form">
      <h3>Complete Your Order</h3>
      
      <div class="form-section">
         <h4>Personal Information</h4>
         <div class="form-row">
            <div class="form-group">
               <label for="name">Full Name</label>
               <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
               <label for="email">Email Address</label>
               <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
               <label for="number">Phone Number</label>
               <input type="tel" id="number" name="number" class="form-control" required placeholder="Enter your phone number">
            </div>
         </div>
      </div>
      
      <div class="form-section">
         <h4>Shipping Address</h4>
         <div class="form-row">
            <div class="form-group">
               <label for="flat">Flat/House Number</label>
               <input type="text" id="flat" name="flat" class="form-control" required placeholder="Enter flat or house number">
            </div>
            
            <div class="form-group">
               <label for="street">Street Address</label>
               <input type="text" id="street" name="street" class="form-control" required placeholder="Enter street address">
            </div>
         </div>
         
         <div class="form-row">
            <div class="form-group">
               <label for="city">City</label>
               <input type="text" id="city" name="city" class="form-control" required placeholder="Enter your city">
            </div>
            
            <div class="form-group">
               <label for="state">State/Province</label>
               <input type="text" id="state" name="state" class="form-control" required placeholder="Enter your state">
            </div>
            
            <div class="form-group">
               <label for="country">Country</label>
               <input type="text" id="country" name="country" class="form-control" required placeholder="Enter your country">
            </div>
            
            <div class="form-group">
               <label for="pin_code">Postal/ZIP Code</label>
               <input type="text" id="pin_code" name="pin_code" class="form-control" required placeholder="Enter postal/ZIP code">
            </div>
         </div>
      </div>
      
      <div class="form-section">
         <h4>Payment Method</h4>
         <input type="hidden" name="method" id="payment_method" value="cash on delivery">
         
         <div class="payment-methods">
            <div class="payment-method selected" data-method="cash on delivery">
               <i class="fas fa-money-bill-wave"></i>
               <div>Cash on Delivery</div>
            </div>
            
            <div class="payment-method" data-method="credit card">
               <i class="fas fa-credit-card"></i>
               <div>Credit Card</div>
            </div>
            
            <div class="payment-method" data-method="paypal">
               <i class="fab fa-paypal"></i>
               <div>PayPal</div>
            </div>
            
            <div class="payment-method" data-method="paytm">
               <i class="fas fa-wallet"></i>
               <div>Paytm</div>
            </div>
         </div>
      </div>
      
      <button type="submit" class="btn-primary" name="order_btn">
         <i class="fas fa-check-circle"></i> Place Order
      </button>
   </form>
</div>

<?php include 'footer.php'; ?>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

<script>
   // Payment method selection
   const paymentMethods = document.querySelectorAll('.payment-method');
   const paymentMethodInput = document.getElementById('payment_method');
   
   paymentMethods.forEach(method => {
      method.addEventListener('click', function() {
         // Remove selected class from all methods
         paymentMethods.forEach(m => m.classList.remove('selected'));
         
         // Add selected class to clicked method
         this.classList.add('selected');
         
         // Update hidden input value
         paymentMethodInput.value = this.dataset.method;
      });
   });
   
   // Form validation enhancement
   const form = document.querySelector('.checkout-form');
   
   form.addEventListener('submit', function(e) {
      if(<?php echo $grand_total; ?> <= 0) {
         e.preventDefault();
         alert('Your cart is empty. Please add items to your cart before checkout.');
      }
   });
</script>

</body>
</html>