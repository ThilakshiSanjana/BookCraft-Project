<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

$recent_order = null;
$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY placed_on DESC LIMIT 1");
if(mysqli_num_rows($order_query) > 0) {
    $recent_order = mysqli_fetch_assoc($order_query);
}

if(isset($_POST['send'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $number = $_POST['number'];
   $msg = mysqli_real_escape_string($conn, $_POST['message']);

   $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('query failed');

   if(mysqli_num_rows($select_message) > 0){
      $message[] = 'message sent already!';
   }else{
      mysqli_query($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES('$user_id', '$name', '$email', '$number', '$msg')") or die('query failed');
      $message[] = 'message sent successfully!';
   }
}


$demo_orders = [
    '1001' => [
        'id' => '1001',
        'placed_on' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'payment_status' => 'completed',
        'total_price' => '149.99',
        'items' => 3
    ],
    '1002' => [
        'id' => '1002',
        'placed_on' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'payment_status' => 'pending',
        'total_price' => '89.50',
        'items' => 2
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Us | BookCraft</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- Leaflet CSS for maps -->
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      :root {
         --primary: #6c5ce7;
         --primary-dark: #5549d1;
         --secondary: #fd79a8;
         --secondary-dark: #e84393;
         --accent: #00cec9;
         --success: #00b894;
         --warning: #fdcb6e;
         --danger: #ff7675;
         --dark: #2d3436;
         --medium: #636e72;
         --light: #b2bec3;
         --white: #ffffff;
         --bg: #f7f9fc;
         --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
         --shadow-md: 0 5px 15px rgba(0,0,0,0.07);
         --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
         --radius-sm: 8px;
         --radius-md: 12px;
         --radius-lg: 20px;
         --transition: all 0.3s ease;
      }

      body {
         background-color: var(--bg);
         font-family: 'Poppins', sans-serif;
         color: var(--dark);
      }

      .contact-hero {
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         padding: 4rem 2rem;
         text-align: center;
         color: var(--white);
         position: relative;
         overflow: hidden;
         margin-bottom: 3rem;
      }

      .contact-hero::before {
         content: '';
         position: absolute;
         top: -10%;
         left: -10%;
         width: 120%;
         height: 120%;
         background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTI4MCAxNDAiIHByZXNlcnZlQXNwZWN0UmF0aW89Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIj48cGF0aCBkPSJNMTI4MCAxNDBWMFM5OTMuNDYgMTQwIDY0MCAxMzkgMCAwIDAgMHYxNDB6Ii8+PC9nPjwvc3ZnPg==');
         background-size: 100% 100%;
         z-index: 1;
         opacity: 0.1;
      }

      .contact-hero .content {
         position: relative;
         z-index: 2;
      }

      .contact-hero h1 {
         font-size: 3rem;
         font-weight: 700;
         margin-bottom: 1rem;
         letter-spacing: -0.5px;
      }

      .contact-hero p {
         font-size: 1.2rem;
         max-width: 700px;
         margin: 0 auto;
         font-weight: 300;
      }

      .page-content {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 2rem;
         margin-bottom: 4rem;
      }

      .tab-container {
         margin-bottom: 2rem;
      }

      .tabs {
         display: flex;
         background-color: var(--white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         overflow: hidden;
         position: relative;
         z-index: 1;
      }

      .tab {
         flex: 1;
         padding: 1.25rem;
         text-align: center;
         font-weight: 600;
         cursor: pointer;
         transition: var(--transition);
         color: var(--medium);
         position: relative;
         overflow: hidden;
         border-bottom: 3px solid transparent;
      }

      .tab.active {
         color: var(--primary);
         border-bottom: 3px solid var(--primary);
      }

      .tab:hover:not(.active) {
         color: var(--dark);
         background-color: rgba(108, 92, 231, 0.05);
      }

      .tab i {
         margin-right: 8px;
      }

      .tab-content {
         display: none;
      }

      .tab-content.active {
         display: block;
         animation: fadeIn 0.5s ease;
      }

      @keyframes fadeIn {
         from { opacity: 0; transform: translateY(10px); }
         to { opacity: 1; transform: translateY(0); }
      }

      .contact-grid {
         display: grid;
         grid-template-columns: 1fr 1.5fr;
         gap: 2.5rem;
      }

      /* Contact Info Panel */
      .contact-info {
         background: var(--white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         padding: 2.5rem;
         position: relative;
         overflow: hidden;
         height: fit-content;
      }
      
      .contact-info::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 6px;
         background: linear-gradient(90deg, var(--primary), var(--secondary));
      }

      .contact-info h3 {
         font-size: 1.75rem;
         font-weight: 600;
         margin-bottom: 2rem;
         color: var(--primary);
         position: relative;
      }

      .info-grid {
         display: grid;
         gap: 1.5rem;
      }

      .info-item {
         display: flex;
         align-items: flex-start;
         gap: 1rem;
      }
      
      .info-icon {
         width: 42px;
         height: 42px;
         min-width: 42px;
         background: rgba(108, 92, 231, 0.1);
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         color: var(--primary);
         font-size: 1.2rem;
         transition: var(--transition);
      }
      
      .info-item:hover .info-icon {
         background: var(--primary);
         color: var(--white);
         transform: scale(1.1);
      }
      
      .info-content h4 {
         font-size: 1rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 0.25rem;
      }
      
      .info-content p {
         color: var(--medium);
         font-size: 0.95rem;
         line-height: 1.5;
      }
      
      .social-links {
         display: flex;
         gap: 1rem;
         margin: 2rem 0;
      }
      
      .social-links a {
         width: 40px;
         height: 40px;
         border-radius: 50%;
         background: rgba(108, 92, 231, 0.1);
         display: flex;
         align-items: center;
         justify-content: center;
         color: var(--primary);
         font-size: 1.2rem;
         transition: var(--transition);
      }
      
      .social-links a:hover {
         background: var(--primary);
         color: var(--white);
         transform: translateY(-5px);
      }
      
      /* Map styling */
      .map-container {
         width: 100%;
         height: 280px;
         border-radius: var(--radius-sm);
         overflow: hidden;
         margin-top: 2rem;
         box-shadow: var(--shadow-sm);
      }
      
      #sri-lanka-map {
         width: 100%;
         height: 100%;
      }

      /* Contact Form Panel */
      .contact-form {
         background: var(--white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         padding: 2.5rem;
         position: relative;
         overflow: hidden;
      }
      
      .contact-form::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 6px;
         background: linear-gradient(90deg, var(--secondary), var(--primary));
      }

      .contact-form h3 {
         font-size: 1.75rem;
         font-weight: 600;
         margin-bottom: 2rem;
         color: var(--primary);
      }
      
      .form-grid {
         display: grid;
         gap: 1.5rem;
      }
      
      .form-row {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.5rem;
      }
      
      .form-group {
         position: relative;
      }
      
      .form-control {
         width: 100%;
         padding: 1rem 1.25rem;
         border: 1px solid #e0e0e0;
         border-radius: var(--radius-sm);
         font-size: 1rem;
         transition: var(--transition);
         background-color: #f9fafc;
         color: var(--dark);
      }
      
      .form-control:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
         background-color: var(--white);
         outline: none;
      }
      
      .form-label {
         position: absolute;
         left: 1rem;
         top: 0;
         padding: 0 0.5rem;
         background: var(--white);
         font-size: 0.85rem;
         font-weight: 500;
         color: var(--primary);
         transform: translateY(-50%);
         transition: var(--transition);
         pointer-events: none;
         z-index: 2;
      }
      
      .form-group textarea {
         min-height: 150px;
         resize: vertical;
      }
      
      .char-count {
         display: flex;
         justify-content: flex-end;
         font-size: 0.8rem;
         color: var(--medium);
         margin-top: 0.5rem;
      }
      
      .file-upload {
         border: 2px dashed #e0e0e0;
         border-radius: var(--radius-sm);
         padding: 1.5rem;
         text-align: center;
         transition: var(--transition);
         cursor: pointer;
         background-color: #f9fafc;
      }
      
      .file-upload:hover {
         border-color: var(--primary);
         background-color: rgba(108, 92, 231, 0.05);
      }
      
      .file-icon {
         font-size: 2rem;
         color: var(--primary);
         margin-bottom: 0.5rem;
      }
      
      .file-info {
         margin-top: 0.75rem;
         font-size: 0.85rem;
         color: var(--medium);
      }
      
      .submit-btn {
         background: linear-gradient(90deg, var(--primary), var(--secondary));
         color: var(--white);
         border: none;
         padding: 1rem 2rem;
         font-size: 1.1rem;
         border-radius: var(--radius-sm);
         cursor: pointer;
         transition: var(--transition);
         font-weight: 500;
         width: 100%;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 0.5rem;
         position: relative;
         overflow: hidden;
         margin-top: 1rem;
      }
      
      .submit-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, var(--secondary), var(--primary));
         opacity: 0;
         transition: var(--transition);
      }
      
      .submit-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 7px 15px rgba(108, 92, 231, 0.3);
      }
      
      .submit-btn:hover::before {
         opacity: 1;
      }
      
      .submit-btn span {
         position: relative;
         z-index: 2;
      }
      
      .submit-btn i {
         position: relative;
         z-index: 2;
      }
      
      /* Alerts and Notifications */
      .alert {
         padding: 1.25rem;
         border-radius: var(--radius-sm);
         margin-bottom: 1.5rem;
         display: flex;
         align-items: flex-start;
         gap: 1rem;
         animation: fadeIn 0.5s ease;
      }
      
      .alert-icon {
         font-size: 1.5rem;
         flex-shrink: 0;
      }
      
      .alert-content {
         flex-grow: 1;
      }
      
      .alert-heading {
         font-weight: 600;
         margin-bottom: 0.25rem;
         font-size: 1.1rem;
      }
      
      .alert-message {
         color: inherit;
         opacity: 0.9;
      }
      
      .alert-success {
         background-color: rgba(0, 184, 148, 0.1);
         border: 1px solid rgba(0, 184, 148, 0.2);
         color: #00b894;
      }
      
      .alert-error {
         background-color: rgba(255, 118, 117, 0.1);
         border: 1px solid rgba(255, 118, 117, 0.2);
         color: #ff7675;
      }
      
      .alert-close {
         color: inherit;
         opacity: 0.7;
         font-size: 1.2rem;
         cursor: pointer;
         transition: var(--transition);
         background: transparent;
         border: none;
      }
      
      .alert-close:hover {
         opacity: 1;
      }
      
      /* Order Tracking Styles */
      .order-tracking {
         background: var(--white);
         border-radius: var(--radius-md);
         box-shadow: var(--shadow-md);
         padding: 2.5rem;
         position: relative;
         overflow: hidden;
      }
      
      .order-tracking::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 6px;
         background: linear-gradient(90deg, var(--accent), var(--success));
      }
      
      .track-form {
         display: flex;
         gap: 1rem;
         margin-bottom: 2rem;
      }
      
      .track-input {
         flex: 1;
         position: relative;
      }
      
      .track-btn {
         background-color: var(--accent);
         color: var(--white);
         border: none;
         padding: 0.85rem 1.5rem;
         font-size: 1rem;
         border-radius: var(--radius-sm);
         cursor: pointer;
         transition: var(--transition);
         font-weight: 500;
      }
      
      .track-btn:hover {
         background-color: #01b3b0;
         transform: translateY(-2px);
      }
      
      .timeline {
         position: relative;
         padding-left: 45px;
         margin-top: 3rem;
      }
      
      .timeline::before {
         content: '';
         position: absolute;
         left: 15px;
         top: 0;
         width: 2px;
         height: 100%;
         background-color: #e0e0e0;
      }
      
      .timeline-item {
         position: relative;
         padding-bottom: 25px;
      }
      
      .timeline-item:last-child {
         padding-bottom: 0;
      }
      
      .timeline-dot {
         position: absolute;
         left: -45px;
         width: 30px;
         height: 30px;
         border-radius: 50%;
         background-color: var(--white);
         border: 2px solid #e0e0e0;
         display: flex;
         align-items: center;
         justify-content: center;
         color: var(--medium);
         font-size: 0.9rem;
      }
      
      .timeline-item.active .timeline-dot {
         background-color: var(--accent);
         border-color: var(--accent);
         color: var(--white);
      }
      
      .timeline-item.completed .timeline-dot {
         background-color: var(--success);
         border-color: var(--success);
         color: var(--white);
      }
      
      .timeline-content {
         background-color: #f9fafc;
         padding: 1.25rem;
         border-radius: var(--radius-sm);
         box-shadow: var(--shadow-sm);
      }
      
      .timeline-date {
         font-size: 0.85rem;
         color: var(--medium);
         margin-bottom: 0.5rem;
      }
      
      .timeline-title {
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 0.5rem;
      }
      
      .timeline-desc {
         font-size: 0.95rem;
         color: var(--medium);
      }
      
      .order-summary {
         margin-top: 2rem;
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 1rem;
      }
      
      .summary-item {
         background-color: #f9fafc;
         padding: 1.25rem;
         border-radius: var(--radius-sm);
         text-align: center;
      }
      
      .summary-title {
         font-size: 0.85rem;
         color: var(--medium);
         margin-bottom: 0.5rem;
      }
      
      .summary-value {
         font-size: 1.25rem;
         font-weight: 600;
         color: var(--dark);
      }
      
      .order-not-found {
         text-align: center;
         padding: 3rem 2rem;
      }
      
      .order-not-found i {
         font-size: 4rem;
         color: var(--light);
         margin-bottom: 1.5rem;
      }
      
      .order-not-found h3 {
         font-size: 1.5rem;
         color: var(--dark);
         margin-bottom: 1rem;
      }
      
      .order-not-found p {
         color: var(--medium);
         max-width: 500px;
         margin: 0 auto 1.5rem;
      }
      
      .view-orders-btn {
         display: inline-flex;
         align-items: center;
         gap: 0.5rem;
         background-color: var(--primary);
         color: var(--white);
         padding: 0.75rem 1.5rem;
         border-radius: var(--radius-sm);
         text-decoration: none;
         font-weight: 500;
         transition: var(--transition);
      }
      
      .view-orders-btn:hover {
         background-color: var(--primary-dark);
         transform: translateY(-2px);
      }

      /* Order results container styles */
      .order-result-container {
         display: none;
         margin-top: 2rem;
      }
      
      .order-result-container.active {
         display: block;
         animation: fadeIn 0.5s ease;
      }

      /* Responsive Design */
      @media (max-width: 992px) {
         .contact-grid {
            grid-template-columns: 1fr;
         }
         
         .contact-hero h1 {
            font-size: 2.5rem;
         }
         
         .order-summary {
            grid-template-columns: 1fr;
         }
      }
      
      @media (max-width: 768px) {
         .contact-hero {
            padding: 3rem 1.5rem;
         }
         
         .contact-hero h1 {
            font-size: 2rem;
         }
         
         .contact-hero p {
            font-size: 1rem;
         }
         
         .page-content {
            padding: 0 1.5rem;
         }
         
         .contact-info, .contact-form, .order-tracking {
            padding: 2rem;
         }
         
         .form-row {
            grid-template-columns: 1fr;
         }
         
         .tabs {
            flex-direction: column;
         }
         
         .tab {
            padding: 1rem;
            border-bottom: none;
            border-left: 3px solid transparent;
         }
         
         .tab.active {
            border-bottom: none;
            border-left: 3px solid var(--primary);
         }
         
         .track-form {
            flex-direction: column;
         }
      }
      
      @media (max-width: 576px) {
         .contact-hero h1 {
            font-size: 1.75rem;
         }
         
         .page-content {
            padding: 0 1rem;
         }
         
         .contact-info, .contact-form, .order-tracking {
            padding: 1.5rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Modern Hero Banner -->
<section class="contact-hero">
   <div class="content">
      <h1>Get in Touch</h1>
      <p>Have questions or feedback? We're here to help! Reach out to our team and we'll get back to you as soon as possible.</p>
   </div>
</section>

<div class="page-content">
   <!-- Tab Navigation -->
   <div class="tab-container">
      <div class="tabs">
         <div class="tab active" data-tab="contact">
            <i class="fas fa-envelope"></i> Contact Us
         </div>
         <div class="tab" data-tab="track">
            <i class="fas fa-truck"></i> Track Your Order
         </div>
         <div class="tab" data-tab="location">
            <i class="fas fa-map-marker-alt"></i> Our Location
         </div>
      </div>
   </div>
   
   <!-- Contact Tab Content -->
   <div class="tab-content active" id="contact-tab">
      <div class="contact-grid">
         <!-- Contact Information Panel -->
         <div class="contact-info">
            <h3>Contact Information</h3>
            
            <div class="info-grid">
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="info-content">
                     <h4>Our Location</h4>
                     <p>123 Galle Road, Colombo 03, Sri Lanka</p>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-envelope"></i>
                  </div>
                  <div class="info-content">
                     <h4>Email Us</h4>
                     <p>support@bookcraft.com</p>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="info-content">
                     <h4>Call Us</h4>
                     <p>+94 11 234 5678</p>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-clock"></i>
                  </div>
                  <div class="info-content">
                     <h4>Working Hours</h4>
                     <p>Monday - Friday: 9am - 5pm</p>
                  </div>
               </div>
            </div>
            
            <div class="social-links">
               <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
               <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
               <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
               <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
         </div>
         
         <!-- Contact Form Panel -->
         <div class="contact-form">
            <!-- Alerts/Notifications -->
            <?php if(isset($message)): ?>
               <?php foreach($message as $msg): ?>
                  <div class="alert <?php echo $msg == 'message sent successfully!' ? 'alert-success' : 'alert-error'; ?>">
                     <div class="alert-icon">
                        <i class="fas <?php echo $msg == 'message sent successfully!' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                     </div>
                     <div class="alert-content">
                        <h4 class="alert-heading"><?php echo $msg == 'message sent successfully!' ? 'Success!' : 'Error!'; ?></h4>
                        <p class="alert-message"><?php echo $msg; ?></p>
                     </div>
                     <button type="button" class="alert-close" aria-label="Close alert">
                        <i class="fas fa-times"></i>
                     </button>
                  </div>
               <?php endforeach; ?>
            <?php endif; ?>
            
            <h3>Send Us a Message</h3>
            
            <form action="" method="post" id="contact-form">
               <div class="form-grid">
                  <div class="form-row">
                     <div class="form-group">
                        <input type="text" id="name" name="name" class="form-control" required>
                        <label for="name" class="form-label">Your Name</label>
                     </div>
                     
                     <div class="form-group">
                        <input type="email" id="email" name="email" class="form-control" required>
                        <label for="email" class="form-label">Your Email</label>
                     </div>
                  </div>
                  
                  <div class="form-group">
                     <input type="tel" id="number" name="number" class="form-control" required>
                     <label for="number" class="form-label">Your Phone Number</label>
                  </div>
                  
                  <div class="form-group">
                     <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                     <label for="message" class="form-label">Your Message</label>
                     <div class="char-count">
                        <span id="current-chars">0</span>/<span id="max-chars">500</span> characters
                     </div>
                  </div>
                  
                  <label for="file-upload" class="file-upload">
                     <div class="file-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                     </div>
                     <p>Drag and drop a file here or click to browse</p>
                     <span class="file-info" id="file-info">Max file size: 5MB</span>
                     <input type="file" id="file-upload" name="attachment" style="display:none;">
                  </label>
                  
                  <button type="submit" name="send" class="submit-btn">
                     <i class="fas fa-paper-plane"></i>
                     <span>Send Message</span>
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
   
   <!-- Order Tracking Tab Content -->
   <div class="tab-content" id="track-tab">
      <div class="order-tracking">
         <h3>Track Your Order</h3>
         
         <div class="track-form">
            <div class="form-group track-input">
               <input type="text" id="order-number" class="form-control" placeholder="Enter your order number" required>
               <label for="order-number" class="form-label">Order Number</label>
            </div>
            <button type="button" id="track-order-btn" class="track-btn">
               <i class="fas fa-search"></i> Track
            </button>
         </div>
         
         <!-- Order tracking results containers -->
         <div id="tracking-results">
            <!-- Dynamic order results - will be shown/hidden via JavaScript -->
            <div id="dynamic-order-result" class="order-result-container">
               <!-- Content will be populated by JavaScript -->
            </div>
            
            <!-- Default recent order display -->
            <?php if($recent_order): ?>
               <div id="recent-order-result" class="order-result-container active">
                  <div class="order-summary">
                     <div class="summary-item">
                        <p class="summary-title">Order Number</p>
                        <p class="summary-value">#<?php echo $recent_order['id']; ?></p>
                     </div>
                     <div class="summary-item">
                        <p class="summary-title">Order Date</p>
                        <p class="summary-value"><?php echo date('d M Y', strtotime($recent_order['placed_on'])); ?></p>
                     </div>
                     <div class="summary-item">
                        <p class="summary-title">Status</p>
                        <p class="summary-value"><?php echo ucfirst($recent_order['payment_status']); ?></p>
                     </div>
                  </div>
                  
                  <div class="timeline">
                     <div class="timeline-item completed">
                        <div class="timeline-dot">
                           <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date"><?php echo date('d M Y, h:i A', strtotime($recent_order['placed_on'])); ?></p>
                           <h4 class="timeline-title">Order Placed</h4>
                           <p class="timeline-desc">Your order has been received and is being processed.</p>
                        </div>
                     </div>
                     
                     <?php if($recent_order['payment_status'] == 'completed'): ?>
                     <div class="timeline-item completed">
                        <div class="timeline-dot">
                           <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date"><?php echo date('d M Y, h:i A', strtotime($recent_order['placed_on'] . ' +1 day')); ?></p>
                           <h4 class="timeline-title">Payment Confirmed</h4>
                           <p class="timeline-desc">Your payment has been confirmed and order is being prepared.</p>
                        </div>
                     </div>
                     
                     <div class="timeline-item active">
                        <div class="timeline-dot">
                           <i class="fas fa-box"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date"><?php echo date('d M Y', strtotime($recent_order['placed_on'] . ' +2 days')); ?></p>
                           <h4 class="timeline-title">Preparing Your Order</h4>
                           <p class="timeline-desc">Your books are being packed and prepared for shipping.</p>
                        </div>
                     </div>
                     
                     <div class="timeline-item">
                        <div class="timeline-dot">
                           <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date">Estimated: <?php echo date('d M Y', strtotime($recent_order['placed_on'] . ' +4 days')); ?></p>
                           <h4 class="timeline-title">Out for Delivery</h4>
                           <p class="timeline-desc">Your package is on its way to you.</p>
                        </div>
                     </div>
                     
                     <div class="timeline-item">
                        <div class="timeline-dot">
                           <i class="fas fa-home"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date">Estimated: <?php echo date('d M Y', strtotime($recent_order['placed_on'] . ' +5 days')); ?></p>
                           <h4 class="timeline-title">Delivered</h4>
                           <p class="timeline-desc">Package will be delivered to your address.</p>
                        </div>
                     </div>
                     <?php else: ?>
                     <div class="timeline-item active">
                        <div class="timeline-dot">
                           <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="timeline-content">
                           <p class="timeline-date">Pending</p>
                           <h4 class="timeline-title">Payment Pending</h4>
                           <p class="timeline-desc">We're waiting for your payment to be confirmed.</p>
                        </div>
                     </div>
                     <?php endif; ?>
                  </div>
               </div>
            <?php else: ?>
               <div id="no-orders" class="order-result-container active">
                  <div class="order-not-found">
                     <i class="fas fa-search"></i>
                     <h3>No Recent Orders Found</h3>
                     <p>We couldn't find any recent orders associated with your account. If you've recently placed an order, please enter the order number above to track it.</p>
                     <a href="shop.php" class="view-orders-btn">
                        <i class="fas fa-book"></i> Browse Books
                     </a>
                  </div>
               </div>
            <?php endif; ?>
            
            <!-- Order not found container -->
            <div id="order-not-found" class="order-result-container">
               <div class="order-not-found">
                  <i class="fas fa-search"></i>
                  <h3>Order Not Found</h3>
                  <p id="not-found-message">We couldn't find this order. Please check your order number and try again.</p>
                  <a href="shop.php" class="view-orders-btn">
                     <i class="fas fa-book"></i> Browse Books
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Location Tab Content -->
   <div class="tab-content" id="location-tab">
      <div class="contact-info">
         <h3>Visit Our Store in Sri Lanka</h3>
         
         <div class="info-grid">
            <div class="info-item">
               <div class="info-icon">
                  <i class="fas fa-map-marker-alt"></i>
               </div>
               <div class="info-content">
                  <h4>Main Bookstore</h4>
                  <p>123 Galle Road, Colombo 03, Sri Lanka</p>
               </div>
            </div>
            
            <div class="info-item">
               <div class="info-icon">
                  <i class="fas fa-store-alt"></i>
               </div>
               <div class="info-content">
                  <h4>Kandy Branch</h4>
                  <p>45 Kandy Road, Kandy, Sri Lanka</p>
               </div>
            </div>
            
            <div class="info-item">
               <div class="info-icon">
                  <i class="fas fa-clock"></i>
               </div>
               <div class="info-content">
                  <h4>Store Hours</h4>
                  <p>Monday - Saturday: 9am - 7pm<br>Sunday: 10am - 4pm</p>
               </div>
            </div>
            
            <div class="info-item">
               <div class="info-icon">
                  <i class="fas fa-phone-alt"></i>
               </div>
               <div class="info-content">
                  <h4>Store Contact</h4>
                  <p>+94 11 234 5678</p>
               </div>
            </div>
         </div>
         
         <div class="map-container">
            <div id="sri-lanka-map"></div>
         </div>
      </div>
   </div>
</div>

<?php include 'footer.php'; ?>

<!-- Leaflet JS for maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
   // Demo order data for tracking examples
   const demoOrders = <?php echo json_encode($demo_orders); ?>;

   // Tab Navigation
   const tabs = document.querySelectorAll('.tab');
   const tabContents = document.querySelectorAll('.tab-content');
   
   tabs.forEach(tab => {
      tab.addEventListener('click', function() {
         const tabId = this.getAttribute('data-tab');
         
         // Remove active class from all tabs and tab contents
         tabs.forEach(t => t.classList.remove('active'));
         tabContents.forEach(c => c.classList.remove('active'));
         
         // Add active class to clicked tab and corresponding content
         this.classList.add('active');
         document.getElementById(`${tabId}-tab`).classList.add('active');
         
         // Initialize map if location tab is active
         if (tabId === 'location') {
            initMap();
         }
      });
   });

   // Sri Lanka Map Initialization
   let map;
   
   function initMap() {
      if (map) return; // Only initialize once
      
      // Create map centered on Sri Lanka
      map = L.map('sri-lanka-map').setView([7.8731, 80.7718], 8);
      
      // Add OpenStreetMap tile layer
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
         attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);
      
      // Add markers for store locations
      const colomboMarker = L.marker([6.9271, 79.8612]).addTo(map)
         .bindPopup('<b>BookCraft Main Store</b><br>123 Galle Road, Colombo 03');
      
      const kandyMarker = L.marker([7.2906, 80.6337]).addTo(map)
         .bindPopup('<b>BookCraft Kandy Branch</b><br>45 Kandy Road, Kandy');
      
      // Open Colombo popup by default
      colomboMarker.openPopup();
   }
   
   // Character counter for textarea
   const messageTextarea = document.getElementById('message');
   const currentChars = document.getElementById('current-chars');
   const maxChars = document.getElementById('max-chars');
   const maxLength = 500;
   
   if (messageTextarea && currentChars && maxChars) {
      maxChars.textContent = maxLength;
      
      messageTextarea.addEventListener('input', function() {
         const remaining = this.value.length;
         currentChars.textContent = remaining;
         
         if (remaining > maxLength) {
            currentChars.style.color = '#ff7675';
         } else {
            currentChars.style.color = '#636e72';
         }
      });
   }
   
   // File upload handling
   const fileUpload = document.getElementById('file-upload');
   const fileInfo = document.getElementById('file-info');
   const fileUploadLabel = document.querySelector('.file-upload');
   
   if (fileUpload && fileInfo && fileUploadLabel) {
      fileUpload.addEventListener('change', function() {
         if (this.files.length > 0) {
            const fileName = this.files[0].name;
            const fileSize = (this.files[0].size / 1024).toFixed(2);
            fileInfo.textContent = `Selected: ${fileName} (${fileSize} KB)`;
            fileUploadLabel.style.borderColor = '#6c5ce7';
            fileUploadLabel.style.backgroundColor = 'rgba(108, 92, 231, 0.05)';
         } else {
            fileInfo.textContent = 'Max file size: 5MB';
            fileUploadLabel.style.borderColor = '#e0e0e0';
            fileUploadLabel.style.backgroundColor = '#f9fafc';
         }
      });
      
      // Drag and drop functionality
      fileUploadLabel.addEventListener('dragover', function(e) {
         e.preventDefault();
         this.style.borderColor = '#6c5ce7';
         this.style.backgroundColor = 'rgba(108, 92, 231, 0.1)';
      });
      
      fileUploadLabel.addEventListener('dragleave', function(e) {
         e.preventDefault();
         this.style.borderColor = '#e0e0e0';
         this.style.backgroundColor = '#f9fafc';
      });
      
      fileUploadLabel.addEventListener('drop', function(e) {
         e.preventDefault();
         this.style.borderColor = '#6c5ce7';
         this.style.backgroundColor = 'rgba(108, 92, 231, 0.05)';
         
         if (e.dataTransfer.files.length > 0) {
            fileUpload.files = e.dataTransfer.files;
            const fileName = e.dataTransfer.files[0].name;
            const fileSize = (e.dataTransfer.files[0].size / 1024).toFixed(2);
            fileInfo.textContent = `Selected: ${fileName} (${fileSize} KB)`;
         }
      });
   }
   
   // Close alert functionality
   const alertCloseButtons = document.querySelectorAll('.alert-close');
   alertCloseButtons.forEach(button => {
      button.addEventListener('click', function() {
         this.parentElement.style.opacity = '0';
         setTimeout(() => {
            this.parentElement.style.display = 'none';
         }, 300);
      });
   });
   
   // Form label animation on focus and blur
   const formControls = document.querySelectorAll('.form-control');
   formControls.forEach(control => {
      // Set initial state for pre-filled fields
      if (control.value !== '') {
         control.parentElement.classList.add('has-value');
      }
      
      control.addEventListener('focus', function() {
         this.parentElement.classList.add('focused');
      });
      
      control.addEventListener('blur', function() {
         this.parentElement.classList.remove('focused');
         if (this.value === '') {
            this.parentElement.classList.remove('has-value');
         } else {
            this.parentElement.classList.add('has-value');
         }
      });
   });
   
   // Form validation and submission
   const contactForm = document.getElementById('contact-form');
   if (contactForm) {
      contactForm.addEventListener('submit', function(e) {
         let valid = true;
         
         // Basic form validation
         const name = document.getElementById('name').value.trim();
         const email = document.getElementById('email').value.trim();
         const number = document.getElementById('number').value.trim();
         const message = document.getElementById('message').value.trim();
         
         if (name === '' || email === '' || number === '' || message === '') {
            valid = false;
         }
         
         if (message.length > maxLength) {
            valid = false;
            currentChars.style.color = '#ff7675';
         }
         
         if (!valid) {
            e.preventDefault();
            alert('Please fill all required fields correctly.');
         }
      });
   }
   
   // FIXED Order tracking functionality
   document.addEventListener('DOMContentLoaded', function() {
      // Helper functions
      function formatDate(dateStr) {
         const date = new Date(dateStr);
         return date.toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
         });
      }
      
      function formatDateTime(dateStr) {
         const date = new Date(dateStr);
         return date.toLocaleString('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
         });
      }
      
      function capitalizeFirst(str) {
         return str.charAt(0).toUpperCase() + str.slice(1);
      }
      
      // Get DOM elements
      const trackBtn = document.getElementById('track-order-btn');
      const orderNumberInput = document.getElementById('order-number');
      const dynamicResult = document.getElementById('dynamic-order-result');
      const recentResult = document.getElementById('recent-order-result');
      const noOrders = document.getElementById('no-orders');
      const orderNotFound = document.getElementById('order-not-found');
      const notFoundMessage = document.getElementById('not-found-message');
      
      // Track order function
      function trackOrder() {
         if (!orderNumberInput || !dynamicResult || !orderNotFound) {
            console.error('Required DOM elements not found');
            return;
         }
         
         const orderNumber = orderNumberInput.value.trim();
         
         if (!orderNumber) {
            alert('Please enter an order number');
            return;
         }
         
         // Hide all result containers
         document.querySelectorAll('.order-result-container').forEach(container => {
            container.classList.remove('active');
         });
         
         // Check if order exists in our demo data
         if (demoOrders[orderNumber]) {
            const orderData = demoOrders[orderNumber];
            
            // Generate HTML for order summary
            let orderHTML = `
               <div class="order-summary">
                  <div class="summary-item">
                     <p class="summary-title">Order Number</p>
                     <p class="summary-value">#${orderData.id}</p>
                  </div>
                  <div class="summary-item">
                     <p class="summary-title">Order Date</p>
                     <p class="summary-value">${formatDate(orderData.placed_on)}</p>
                  </div>
                  <div class="summary-item">
                     <p class="summary-title">Status</p>
                     <p class="summary-value">${capitalizeFirst(orderData.payment_status)}</p>
                  </div>
               </div>
               
               <div class="timeline">
                  <div class="timeline-item completed">
                     <div class="timeline-dot">
                        <i class="fas fa-check"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">${formatDateTime(orderData.placed_on)}</p>
                        <h4 class="timeline-title">Order Placed</h4>
                        <p class="timeline-desc">Your order has been received and is being processed.</p>
                     </div>
                  </div>`;
                  
            if (orderData.payment_status === 'completed') {
               const paymentDate = new Date(orderData.placed_on);
               paymentDate.setDate(paymentDate.getDate() + 1);
               
               const prepDate = new Date(orderData.placed_on);
               prepDate.setDate(prepDate.getDate() + 2);
               
               const shipDate = new Date(orderData.placed_on);
               shipDate.setDate(shipDate.getDate() + 4);
               
               const deliveryDate = new Date(orderData.placed_on);
               deliveryDate.setDate(deliveryDate.getDate() + 5);
               
               orderHTML += `
                  <div class="timeline-item completed">
                     <div class="timeline-dot">
                        <i class="fas fa-check"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">${formatDateTime(paymentDate)}</p>
                        <h4 class="timeline-title">Payment Confirmed</h4>
                        <p class="timeline-desc">Your payment has been confirmed and order is being prepared.</p>
                     </div>
                  </div>
                  
                  <div class="timeline-item active">
                     <div class="timeline-dot">
                        <i class="fas fa-box"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">${formatDate(prepDate)}</p>
                        <h4 class="timeline-title">Preparing Your Order</h4>
                        <p class="timeline-desc">Your books are being packed and prepared for shipping.</p>
                     </div>
                  </div>
                  
                  <div class="timeline-item">
                     <div class="timeline-dot">
                        <i class="fas fa-shipping-fast"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">Estimated: ${formatDate(shipDate)}</p>
                        <h4 class="timeline-title">Out for Delivery</h4>
                        <p class="timeline-desc">Your package is on its way to you.</p>
                     </div>
                  </div>
                  
                  <div class="timeline-item">
                     <div class="timeline-dot">
                        <i class="fas fa-home"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">Estimated: ${formatDate(deliveryDate)}</p>
                        <h4 class="timeline-title">Delivered</h4>
                        <p class="timeline-desc">Package will be delivered to your address.</p>
                     </div>
                  </div>`;
            } else {
               orderHTML += `
                  <div class="timeline-item active">
                     <div class="timeline-dot">
                        <i class="fas fa-credit-card"></i>
                     </div>
                     <div class="timeline-content">
                        <p class="timeline-date">Pending</p>
                        <h4 class="timeline-title">Payment Pending</h4>
                        <p class="timeline-desc">We're waiting for your payment to be confirmed.</p>
                     </div>
                  </div>`;
            }
            
            orderHTML += `</div>`;
            
            // Update dynamic result container with the generated HTML
            dynamicResult.innerHTML = orderHTML;
            dynamicResult.classList.add('active');
         } else {
            // Update the not found message with the order number
            if (notFoundMessage) {
               notFoundMessage.textContent = `We couldn't find order #${orderNumber}. Please check your order number and try again.`;
            }
            
            // Show the order not found container
            orderNotFound.classList.add('active');
         }
      }
      
      // Set up event listeners
      if (trackBtn) {
         trackBtn.addEventListener('click', trackOrder);
      }
      
      if (orderNumberInput) {
         orderNumberInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
               e.preventDefault();
               trackOrder();
            }
         });
      }
   });
</script>

</body>
</html>