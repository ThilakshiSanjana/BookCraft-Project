<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['add_to_cart'])){
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO cart(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }
}

// Since the products table doesn't have a category column, we'll use static categories
$categories = ['Fiction', 'Non-Fiction', 'Educational', 'Children', 'Self-Help'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BookCraft | Luxury Book Collection</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
   
   <!-- GSAP and Plugins -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/TextPlugin.min.js"></script>
   <script src="https://unpkg.com/split-type"></script>
   
   <!-- Three.js and extensions -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.min.js"></script>
   
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      :root {
         --primary: #121212;
         --secondary: #8a6d46;
         --light: #f8f5f0;
         --dark: #2d2d2d;
         --accent: #d4af37;
         --white: #ffffff;
         --gray: #f4f4f4;
         --shadow: 0 5px 15px rgba(0,0,0,0.05);
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         transition: all 0.3s ease;
      }
      
      body {
         font-family: 'Poppins', sans-serif;
         background-color: var(--light);
         color: var(--dark);
         overflow-x: hidden;
         cursor: default;
      }
      
      /* Custom cursor */
      .cursor {
         position: fixed;
         width: 20px;
         height: 20px;
         border-radius: 50%;
         background-color: rgba(212, 175, 55, 0.3);
         pointer-events: none;
         mix-blend-mode: difference;
         z-index: 9999;
         transform: translate(-50%, -50%) scale(1);
         transition: transform 0.1s ease;
      }
      
      .cursor-follower {
         position: fixed;
         width: 40px;
         height: 40px;
         border-radius: 50%;
         border: 1px solid var(--accent);
         pointer-events: none;
         z-index: 9998;
         transform: translate(-50%, -50%) scale(1);
         transition: transform 0.3s ease, opacity 0.3s ease;
         opacity: 0.5;
      }
      
      /* Loader */
      .loader {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: var(--primary);
         display: flex;
         justify-content: center;
         align-items: center;
         z-index: 9999;
         overflow: hidden;
      }
      
      .loader-content {
         text-align: center;
         color: var(--accent);
         position: relative;
      }
      
      .loader-logo {
         font-family: 'Playfair Display', serif;
         font-size: 3rem;
         margin-bottom: 30px;
         letter-spacing: 4px;
         overflow: hidden;
         position: relative;
      }
      
      .loader-logo span {
         display: inline-block;
         transform: translateY(100%);
         opacity: 0;
      }
      
      .loader-bar {
         width: 200px;
         height: 4px;
         background-color: rgba(255,255,255,0.1);
         border-radius: 4px;
         overflow: hidden;
         position: relative;
         margin: 0 auto;
      }
      
      .loader-progress {
         position: absolute;
         top: 0;
         left: 0;
         height: 100%;
         width: 0%;
         background-color: var(--accent);
      }
      
      .loader-counter {
         margin-top: 15px;
         font-size: 1rem;
         font-weight: 300;
         letter-spacing: 2px;
         opacity: 0.8;
      }
      
      .loader-backdrop {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         z-index: -1;
      }
      
      /* Buttons */
      .btn {
         display: inline-block;
         padding: 12px 25px;
         background-color: var(--secondary);
         color: var(--white);
         font-weight: 500;
         border: none;
         border-radius: 4px;
         cursor: pointer;
         font-size: 14px;
         text-transform: uppercase;
         letter-spacing: 1px;
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      
      .btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
         transform: translateX(-100%);
         transition: 0.6s;
      }
      
      .btn:hover {
         background-color: var(--primary);
         transform: translateY(-3px);
         box-shadow: 0 10px 20px rgba(0,0,0,0.15);
      }
      
      .btn:hover::before {
         transform: translateX(100%);
      }
      
      /* Hero Banner */
      .hero-banner {
         background: linear-gradient(to right, rgba(18, 18, 18, 0.95), rgba(18, 18, 18, 0.8)), url('images/book-banner.jpg');
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         color: var(--white);
         padding: 150px 20px 120px;
         text-align: center;
         position: relative;
         overflow: hidden;
         min-height: 70vh;
         display: flex;
         flex-direction: column;
         justify-content: center;
      }
      
      .hero-canvas {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         z-index: 1;
      }
      
      .hero-content {
         position: relative;
         z-index: 2;
         max-width: 1000px;
         margin: 0 auto;
      }
      
      .hero-title {
         font-family: 'Playfair Display', serif;
         font-size: 4.5rem;
         font-weight: 600;
         margin-bottom: 20px;
         color: var(--white);
         line-height: 1.2;
         overflow: hidden;
      }
      
      .hero-title .word {
         overflow: hidden;
         display: inline-block;
         margin-right: 15px;
      }
      
      .hero-title .char {
         display: inline-block;
         transform: translateY(100%);
         opacity: 0;
      }
      
      .hero-description {
         font-size: 1.1rem;
         max-width: 600px;
         margin: 0 auto 40px;
         line-height: 1.6;
         color: rgba(255, 255, 255, 0.8);
         overflow: hidden;
      }
      
      .hero-description p {
         transform: translateY(30px);
         opacity: 0;
      }
      
      .browse-btn {
         opacity: 0;
         transform: translateY(20px);
         display: inline-flex;
         align-items: center;
         gap: 10px;
         padding: 15px 30px;
      }
      
      .browse-btn i {
         transition: transform 0.3s ease;
      }
      
      .browse-btn:hover i {
         transform: translateX(5px);
      }
      
      .breadcrumb {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
         color: var(--accent);
         font-size: 14px;
         margin-top: 40px;
         opacity: 0;
      }
      
      .breadcrumb a {
         color: var(--accent);
         text-decoration: none;
         transition: color 0.3s ease;
      }
      
      .breadcrumb a:hover {
         color: var(--white);
      }
      
      .scroll-down {
         position: absolute;
         bottom: 30px;
         left: 50%;
         transform: translateX(-50%);
         color: var(--white);
         text-align: center;
         cursor: pointer;
         z-index: 2;
         opacity: 0;
         display: flex;
         flex-direction: column;
         align-items: center;
      }
      
      .scroll-down-circle {
         width: 50px;
         height: 50px;
         border: 1px solid rgba(255,255,255,0.3);
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         margin-bottom: 10px;
         position: relative;
         overflow: hidden;
      }
      
      .scroll-down-circle::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(255,255,255,0.1);
         transform: scale(0);
         border-radius: 50%;
         transition: transform 0.3s ease;
      }
      
      .scroll-down:hover .scroll-down-circle::before {
         transform: scale(1);
      }
      
      .scroll-down i {
         font-size: 18px;
      }
      
      .scroll-down span {
         display: block;
         font-size: 12px;
         margin-top: 5px;
         text-transform: uppercase;
         letter-spacing: 2px;
         font-weight: 300;
      }
      
      /* Search section */
      .search-section {
         background-color: var(--white);
         padding: 30px;
         margin-bottom: 40px;
         border-radius: 10px;
         box-shadow: var(--shadow);
         opacity: 0;
         transform: translateY(20px);
      }
      
      .search-form {
         display: flex;
         flex-wrap: wrap;
         gap: 15px;
      }
      
      .search-input-group {
         flex: 1;
         min-width: 280px;
         position: relative;
      }
      
      .search-input {
         width: 100%;
         height: 50px;
         padding: 10px 20px;
         border: 1px solid #ddd;
         border-radius: 30px;
         font-family: 'Poppins', sans-serif;
         font-size: 16px;
         transition: all 0.3s ease;
         background-color: var(--light);
      }
      
      .search-input:focus {
         border-color: var(--secondary);
         outline: none;
         box-shadow: 0 0 0 2px rgba(138, 109, 70, 0.2);
      }
      
      .search-icon {
         position: absolute;
         top: 50%;
         right: 20px;
         transform: translateY(-50%);
         color: var(--secondary);
         font-size: 18px;
         pointer-events: none;
      }
      
      .search-btn {
         padding: 12px 30px;
         height: 50px;
         display: flex;
         align-items: center;
         gap: 10px;
      }
      
      .search-results {
         margin-top: 20px;
         padding-top: 20px;
         border-top: 1px solid #eee;
         display: none;
      }
      
      .search-results.active {
         display: block;
      }
      
      .search-results-heading {
         font-family: 'Playfair Display', serif;
         font-size: 1.2rem;
         margin-bottom: 15px;
         color: var(--primary);
      }
      
      .search-results-count {
         color: var(--secondary);
         font-weight: 600;
      }
      
      /* Products Section */
      .products {
         padding: 120px 5%;
         max-width: 1400px;
         margin: 0 auto;
         position: relative;
      }
      
      .floating-particles {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         pointer-events: none;
         z-index: -1;
      }
      
      .title-container {
         position: relative;
         text-align: center;
         margin-bottom: 40px;
         opacity: 0;
      }
      
      .title {
         font-family: 'Playfair Display', serif;
         font-size: 3rem;
         font-weight: 600;
         color: var(--primary);
         display: inline-block;
         position: relative;
         padding-bottom: 20px;
      }
      
      .title::after {
         content: '';
         position: absolute;
         width: 100px;
         height: 3px;
         background-color: var(--accent);
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
      }
      
      .subtitle {
         color: var(--secondary);
         font-size: 1.1rem;
         margin-top: 15px;
         font-weight: 300;
         max-width: 700px;
         margin-left: auto;
         margin-right: auto;
      }
      
      .filter-container {
         display: flex;
         justify-content: center;
         align-items: center;
         margin-bottom: 50px;
         flex-wrap: wrap;
         gap: 15px;
         opacity: 0;
         transform: translateY(20px);
      }
      
      .filter-btn {
         padding: 8px 20px;
         background-color: transparent;
         border: 1px solid var(--secondary);
         color: var(--secondary);
         border-radius: 30px;
         cursor: pointer;
         font-size: 0.9rem;
         transition: all 0.3s ease;
      }
      
      .filter-btn.active, .filter-btn:hover {
         background-color: var(--secondary);
         color: var(--white);
      }
      
      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
         gap: 40px;
         perspective: 1000px;
      }
      
      .box {
         background-color: var(--white);
         border-radius: 10px;
         overflow: hidden;
         box-shadow: var(--shadow);
         position: relative;
         transition: transform 0.5s ease, box-shadow 0.5s ease;
         display: flex;
         flex-direction: column;
         height: 100%;
         opacity: 0;
         transform: translateY(30px) rotateX(10deg);
         transform-origin: top;
         transform-style: preserve-3d;
      }
      
      .box:hover {
         transform: translateY(-15px) rotateX(0deg);
         box-shadow: 0 25px 50px rgba(0,0,0,0.15);
      }
      
      .box .image-container {
         position: relative;
         overflow: hidden;
         height: 300px;
      }
      
      .box .image {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.7s ease;
      }
      
      .box:hover .image {
         transform: scale(1.1);
      }
      
      .discount-badge {
         position: absolute;
         top: 15px;
         right: 15px;
         background-color: var(--accent);
         color: var(--primary);
         font-weight: 600;
         padding: 5px 12px;
         border-radius: 30px;
         font-size: 0.9rem;
         z-index: 3;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         transform: translateX(20px);
         opacity: 0;
      }
      
      .stock-status {
         position: absolute;
         top: 15px;
         left: 15px;
         background-color: rgba(18, 18, 18, 0.8);
         color: var(--white);
         font-weight: 500;
         padding: 5px 12px;
         border-radius: 30px;
         font-size: 0.8rem;
         z-index: 3;
         transform: translateX(-20px);
         opacity: 0;
      }
      
      .box .content {
         padding: 30px 25px;
         flex-grow: 1;
         display: flex;
         flex-direction: column;
         position: relative;
      }
      
      .box .category {
         font-size: 0.85rem;
         color: var(--secondary);
         margin-bottom: 5px;
         letter-spacing: 1px;
         text-transform: uppercase;
      }
      
      .box .name {
         font-family: 'Playfair Display', serif;
         font-size: 1.4rem;
         font-weight: 600;
         margin-bottom: 10px;
         color: var(--primary);
         line-height: 1.4;
      }
      
      .box .author {
         font-size: 0.9rem;
         color: var(--dark);
         opacity: 0.8;
         margin-bottom: 15px;
      }
      
      .box .description {
         font-size: 0.9rem;
         color: var(--dark);
         margin-bottom: 20px;
         line-height: 1.6;
         display: -webkit-box;
         -webkit-line-clamp: 3;
         -webkit-box-orient: vertical;
         overflow: hidden;
         opacity: 0.8;
      }
      
      .box .price-container {
         display: flex;
         align-items: center;
         gap: 10px;
         margin-bottom: 25px;
      }
      
      .box .price {
         font-size: 1.5rem;
         font-weight: 600;
         color: var(--secondary);
      }
      
      .box .old-price {
         font-size: 1.1rem;
         text-decoration: line-through;
         color: #999;
      }
      
      .box .qty-container {
         display: flex;
         align-items: center;
         margin-bottom: 25px;
         gap: 15px;
      }
      
      .box .qty-label {
         font-size: 0.9rem;
         color: var(--dark);
      }
      
      .qty-custom {
         display: flex;
         align-items: center;
         width: 120px;
         height: 40px;
         border: 1px solid #ddd;
         border-radius: 40px;
         overflow: hidden;
         background-color: var(--light);
      }
      
      .qty-btn {
         width: 40px;
         height: 40px;
         background: transparent;
         border: none;
         font-size: 16px;
         cursor: pointer;
         transition: background 0.3s;
         color: var(--dark);
      }
      
      .qty-btn:hover {
         background: rgba(0,0,0,0.05);
      }
      
      .qty-custom input {
         width: 40px;
         height: 40px;
         border: none;
         text-align: center;
         font-size: 14px;
         background: transparent;
         color: var(--dark);
         font-weight: 500;
      }
      
      .box .cart-btn {
         margin-top: auto;
         width: 100%;
         padding: 14px 25px;
      }
      
      .box .cart-btn i {
         margin-right: 8px;
      }
      
      .box .cart-btn:hover i {
         animation: bounce 0.5s ease infinite;
      }
      
      .box .details-btn {
         position: absolute;
         top: -20px;
         right: 25px;
         width: 40px;
         height: 40px;
         background-color: var(--white);
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         cursor: pointer;
         color: var(--primary);
         transition: all 0.3s ease;
         opacity: 0;
         transform: translateY(10px);
      }
      
      .box:hover .details-btn {
         opacity: 1;
         transform: translateY(0);
      }
      
      .box .details-btn:hover {
         background-color: var(--primary);
         color: var(--white);
      }
      
      .empty {
         text-align: center;
         font-size: 1.3rem;
         color: var(--dark);
         padding: 80px 0;
         font-family: 'Playfair Display', serif;
      }
      
      /* No results */
      .no-results {
         text-align: center;
         padding: 60px 0;
         display: none;
      }
      
      .no-results.active {
         display: block;
      }
      
      .no-results-icon {
         font-size: 3rem;
         color: var(--secondary);
         margin-bottom: 20px;
         opacity: 0.5;
      }
      
      .no-results-title {
         font-family: 'Playfair Display', serif;
         font-size: 1.8rem;
         margin-bottom: 15px;
      }
      
      .no-results-message {
         font-size: 1.1rem;
         margin-bottom: 25px;
         color: #777;
      }
      
      /* Cart notification */
      .cart-notification {
         position: fixed;
         bottom: 30px;
         right: 30px;
         background-color: var(--primary);
         color: var(--white);
         padding: 15px 25px 15px 20px;
         border-radius: 50px;
         box-shadow: 0 10px 30px rgba(0,0,0,0.2);
         display: flex;
         align-items: center;
         gap: 15px;
         transform: translateX(calc(100% + 30px));
         opacity: 0;
         z-index: 1000;
      }
      
      .cart-notification-icon {
         width: 40px;
         height: 40px;
         background-color: var(--accent);
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         color: var(--primary);
         font-size: 18px;
      }
      
      .cart-notification-content {
         font-size: 0.9rem;
      }
      
      .cart-notification-content strong {
         display: block;
         font-size: 1rem;
         margin-bottom: 3px;
      }
      
      /* Scroll progress */
      .scroll-progress {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 3px;
         background-color: rgba(212, 175, 55, 0.3);
         z-index: 9997;
      }
      
      .scroll-progress-bar {
         height: 100%;
         background-color: var(--accent);
         width: 0%;
      }
      
      /* Animations */
      @keyframes bounce {
         0%, 100% {
            transform: translateY(0);
         }
         50% {
            transform: translateY(-5px);
         }
      }
      
      @keyframes float {
         0%, 100% {
            transform: translateY(0);
         }
         50% {
            transform: translateY(-10px);
         }
      }
      
      @keyframes pulse {
         0%, 100% {
            transform: scale(1);
         }
         50% {
            transform: scale(1.05);
         }
      }
      
      /* Responsive */
      @media (max-width: 1200px) {
         .hero-title {
            font-size: 3.8rem;
         }
         
         .box-container {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         }
      }
      
      @media (max-width: 768px) {
         .hero-title {
            font-size: 3rem;
         }
         
         .hero-banner {
            padding: 100px 20px 80px;
         }
         
         .products {
            padding: 80px 20px;
         }
         
         .title {
            font-size: 2.5rem;
         }
         
         .box-container {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
         }
         
         .cursor, .cursor-follower {
            display: none;
         }
      }
      
      @media (max-width: 480px) {
         .hero-title {
            font-size: 2.3rem;
         }
         
         .hero-description {
            font-size: 1rem;
         }
         
         .products {
            padding: 60px 15px;
         }
         
         .box-container {
            grid-template-columns: 1fr;
            max-width: 300px;
            margin: 0 auto;
         }
         
         .search-btn {
            width: 100%;
            justify-content: center;
         }
      }
   </style>
</head>
<body>
   <!-- Custom cursor -->
   <div class="cursor"></div>
   <div class="cursor-follower"></div>
   
   <!-- Scroll progress bar -->
   <div class="scroll-progress">
      <div class="scroll-progress-bar"></div>
   </div>
   
   <!-- Loader -->
   <div class="loader">
      <div class="loader-backdrop" id="loader-backdrop"></div>
      <div class="loader-content">
         <div class="loader-logo">
            <span>B</span><span>o</span><span>o</span><span>k</span><span>C</span><span>r</span><span>a</span><span>f</span><span>t</span>
         </div>
         <div class="loader-bar">
            <div class="loader-progress"></div>
         </div>
         <div class="loader-counter">0%</div>
      </div>
   </div>
   
   <!-- Cart notification -->
   <div class="cart-notification" id="cart-notification">
      <div class="cart-notification-icon">
         <i class="fas fa-check"></i>
      </div>
      <div class="cart-notification-content">
         <strong>Added to Cart</strong>
         <span>Item successfully added to your cart</span>
      </div>
   </div>
   
   <?php include 'header.php'; ?>

   <section class="hero-banner">
      <div class="hero-canvas" id="hero-canvas"></div>
      <div class="hero-content">
         <h1 class="hero-title">Curated Luxury Book Collection</h1>
         <div class="hero-description">
            <p>Discover our handpicked selection of premium books that blend artistry with literary excellence, crafted for the discerning reader who appreciates the finer details.</p>
         </div>
         <a href="#collection" class="btn browse-btn">Browse Collection <i class="fas fa-arrow-right"></i></a>
         <div class="breadcrumb">
            <a href="home.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Shop</span>
         </div>
      </div>
      <div class="scroll-down" id="scroll-down">
         <div class="scroll-down-circle">
            <i class="fas fa-chevron-down"></i>
         </div>
         <span>Discover</span>
      </div>
   </section>

   <section class="products" id="collection">
      <div class="floating-particles" id="particles"></div>
      <div class="title-container">
         <h2 class="title">Featured Collection</h2>
         <p class="subtitle">Immerse yourself in our carefully curated selection of literary masterpieces, each title chosen for its exceptional craftsmanship and enduring value.</p>
      </div>
      
      <!-- Search Section -->
      <div class="search-section">
         <form id="search-form" class="search-form">
            <div class="search-input-group">
               <input type="text" id="search-input" class="search-input" placeholder="Search for books by title, author, or category..." autocomplete="off">
               <i class="fas fa-search search-icon"></i>
            </div>
            <button type="submit" class="btn search-btn">
               <i class="fas fa-search"></i> Search
            </button>
         </form>
         
         <div id="search-results" class="search-results">
            <h3 class="search-results-heading">
               Found <span id="search-results-count" class="search-results-count">0</span> results for "<span id="search-query"></span>"
            </h3>
         </div>
      </div>
      
      <div class="filter-container">
         <button class="filter-btn active" data-category="all">All Books</button>
         <?php foreach($categories as $category): ?>
         <button class="filter-btn" data-category="<?php echo strtolower($category); ?>"><?php echo $category; ?></button>
         <?php endforeach; ?>
         <?php if(!in_array('Stationery', $categories)): ?>
         <button class="filter-btn" data-category="stationery">Stationery Items</button>
         <?php endif; ?>
      </div>

      <div class="box-container" id="products-container">
         <?php  
            $select_products = mysqli_query($conn, "SELECT * FROM products") or die('query failed');
            if(mysqli_num_rows($select_products) > 0){
               $i = 0;
               while($fetch_products = mysqli_fetch_assoc($select_products)){
                  $i++;
                  
                  // Check if category exists in the database, if not use default
                  $category = isset($fetch_products['category']) ? $fetch_products['category'] : 'Fiction';
                  
                  // Check if description exists in the database, if not use default
                  $description = isset($fetch_products['description']) 
                     ? $fetch_products['description'] 
                     : 'A captivating story that takes readers on an unforgettable journey through imagination and reality.';
         ?>
         <form action="" method="post" class="box" data-index="<?php echo $i; ?>" data-category="<?php echo strtolower($category); ?>" data-name="<?php echo strtolower($fetch_products['name']); ?>">
            <div class="image-container">
               <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo $fetch_products['name']; ?>">
               <div class="discount-badge">-20%</div>
               <div class="stock-status">In Stock</div>
            </div>
            <div class="content">
               <div class="category"><?php echo $category; ?></div>
               <h3 class="name"><?php echo $fetch_products['name']; ?></h3>
               <div class="author">By John Author</div>
               <div class="description"><?php echo $description; ?></div>
               <div class="price-container">
                  <div class="price">Rs<?php echo $fetch_products['price']; ?></div>
                  <div class="old-price">Rs<?php echo number_format($fetch_products['price'] * 1.2, 2); ?></div>
               </div>
               <div class="qty-container">
                  <span class="qty-label">Quantity:</span>
                  <div class="qty-custom">
                     <button type="button" class="qty-btn qty-decrease">
                        <i class="fas fa-minus"></i>
                     </button>
                     <input type="number" min="1" name="product_quantity" value="1" class="qty" readonly>
                     <button type="button" class="qty-btn qty-increase">
                        <i class="fas fa-plus"></i>
                     </button>
                  </div>
               </div>
               <a href="#" class="details-btn">
                  <i class="fas fa-info"></i>
               </a>
               <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
               <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
               <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
               <button type="submit" name="add_to_cart" class="btn cart-btn">
                  <i class="fas fa-shopping-cart"></i> Add to Cart
               </button>
            </div>
         </form>
         <?php
               }
            } else {
               echo '<p class="empty">Our collection is currently being refreshed. Please check back soon for exciting new titles.</p>';
            }
         ?>
      </div>
      
      <!-- No results message -->
      <div id="no-results" class="no-results">
         <div class="no-results-icon">
            <i class="fas fa-search"></i>
         </div>
         <h3 class="no-results-title">No matching results found</h3>
         <p class="no-results-message">Try adjusting your search or filter to find what you're looking for.</p>
         <button id="reset-filters" class="btn">Reset All Filters</button>
      </div>
   </section>

   <?php include 'footer.php'; ?>

   <!-- Custom JS file link -->
   <script src="js/script.js"></script>
   
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         // Register GSAP plugins
         gsap.registerPlugin(ScrollTrigger, ScrollToPlugin, TextPlugin);
         
         // Custom cursor
         const cursor = document.querySelector('.cursor');
         const cursorFollower = document.querySelector('.cursor-follower');
         
         document.addEventListener('mousemove', function(e) {
            gsap.to(cursor, {
               x: e.clientX,
               y: e.clientY,
               duration: 0.1
            });
            
            gsap.to(cursorFollower, {
               x: e.clientX,
               y: e.clientY,
               duration: 0.3
            });
         });
         
         document.addEventListener('mousedown', function() {
            gsap.to(cursor, {
               scale: 0.8,
               duration: 0.2
            });
            
            gsap.to(cursorFollower, {
               scale: 0.7,
               opacity: 0.7,
               duration: 0.2
            });
         });
         
         document.addEventListener('mouseup', function() {
            gsap.to(cursor, {
               scale: 1,
               duration: 0.2
            });
            
            gsap.to(cursorFollower, {
               scale: 1,
               opacity: 0.5,
               duration: 0.2
            });
         });
         
         // Button hover effect
         const buttons = document.querySelectorAll('button, .btn, a');
         buttons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
               gsap.to(cursor, {
                  scale: 1.5,
                  backgroundColor: 'rgba(212, 175, 55, 0.5)',
                  duration: 0.3
               });
               
               gsap.to(cursorFollower, {
                  scale: 1.5,
                  backgroundColor: 'rgba(212, 175, 55, 0.1)',
                  duration: 0.3
               });
            });
            
            btn.addEventListener('mouseleave', function() {
               gsap.to(cursor, {
                  scale: 1,
                  backgroundColor: 'rgba(212, 175, 55, 0.3)',
                  duration: 0.3
               });
               
               gsap.to(cursorFollower, {
                  scale: 1,
                  backgroundColor: 'transparent',
                  duration: 0.3
               });
            });
         });
         
         // Scroll progress
         ScrollTrigger.create({
            start: 'top top',
            end: 'bottom bottom',
            onUpdate: (self) => {
               gsap.to('.scroll-progress-bar', {
                  width: `${self.progress * 100}%`,
                  duration: 0.3,
                  ease: 'power1.out'
               });
            }
         });
         
         // Advanced Loader Animation
         const loaderTl = gsap.timeline();
         
         // Animate each letter of the logo
         gsap.utils.toArray('.loader-logo span').forEach((span, i) => {
            loaderTl.to(span, {
               y: 0,
               opacity: 1,
               duration: 0.5,
               ease: 'back.out(1.7)'
            }, i * 0.1);
         });
         
         // Update loader progress
         let loadProgress = { value: 0 };
         loaderTl.to(loadProgress, {
            value: 100,
            duration: 2,
            ease: 'power2.inOut',
            onUpdate: () => {
               document.querySelector('.loader-progress').style.width = `${loadProgress.value}%`;
               document.querySelector('.loader-counter').textContent = `${Math.round(loadProgress.value)}%`;
            }
         }, 0.5);
         
         // Hide loader
         loaderTl.to('.loader', {
            opacity: 0,
            duration: 0.5,
            onComplete: () => {
               document.querySelector('.loader').style.display = 'none';
               startPageAnimations();
            }
         }, '+=0.5');
         
         // Three.js Loader Background
         const loaderBackdrop = document.getElementById('loader-backdrop');
         const loaderScene = new THREE.Scene();
         const loaderCamera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
         const loaderRenderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
         
         loaderRenderer.setSize(window.innerWidth, window.innerHeight);
         loaderRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
         loaderBackdrop.appendChild(loaderRenderer.domElement);
         
         // Create particles for loader
         const loaderParticlesGeometry = new THREE.BufferGeometry();
         const loaderParticlesCount = 300;
         
         const loaderPosArray = new Float32Array(loaderParticlesCount * 3);
         
         for(let i = 0; i < loaderParticlesCount * 3; i++) {
            loaderPosArray[i] = (Math.random() - 0.5) * 15;
         }
         
         loaderParticlesGeometry.setAttribute('position', new THREE.BufferAttribute(loaderPosArray, 3));
         
         const loaderParticlesMaterial = new THREE.PointsMaterial({
            size: 0.05,
            color: 0xd4af37,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
         });
         
         const loaderParticlesMesh = new THREE.Points(loaderParticlesGeometry, loaderParticlesMaterial);
         loaderScene.add(loaderParticlesMesh);
         
         loaderCamera.position.z = 5;
         
         // Animation
         const animateLoader = function() {
            if (document.querySelector('.loader').style.display === 'none') return;
            
            requestAnimationFrame(animateLoader);
            
            loaderParticlesMesh.rotation.x += 0.001;
            loaderParticlesMesh.rotation.y += 0.002;
            
            loaderRenderer.render(loaderScene, loaderCamera);
         };
         
         animateLoader();
         
         // Page Animations
         function startPageAnimations() {
            // Hero Section Text Animation with SplitType
            const heroTitle = new SplitType('.hero-title', { types: 'chars, words' });
            
            const heroTl = gsap.timeline();
            
            // Animate each character of the title
            heroTl.to(heroTitle.chars, {
               y: 0,
               opacity: 1,
               duration: 0.8,
               stagger: 0.03,
               ease: 'power4.out'
            });
            
            // Animate the description
            heroTl.to('.hero-description p', {
               y: 0,
               opacity: 1,
               duration: 1,
               ease: 'power3.out'
            }, '-=0.4');
            
            // Animate the button and breadcrumb
            heroTl.to('.browse-btn', {
               y: 0,
               opacity: 1,
               duration: 0.8,
               ease: 'power3.out'
            }, '-=0.6');
            
            heroTl.to('.breadcrumb', {
               opacity: 1,
               duration: 0.8,
               ease: 'power3.out'
            }, '-=0.6');
            
            heroTl.to('.scroll-down', {
               opacity: 1,
               duration: 0.8,
               ease: 'power3.out'
            }, '-=0.6');
            
            // Products Section Animations
            // Title container
            gsap.to('.title-container', {
               opacity: 1,
               duration: 1,
               ease: 'power3.out',
               scrollTrigger: {
                  trigger: '.title-container',
                  start: 'top 80%',
                  toggleActions: 'play none none none'
               }
            });
            
            // Search section
            gsap.to('.search-section', {
               opacity: 1,
               y: 0,
               duration: 0.8,
               ease: 'power3.out',
               scrollTrigger: {
                  trigger: '.search-section',
                  start: 'top 85%',
                  toggleActions: 'play none none none'
               }
            });
            
            // Filter buttons
            gsap.to('.filter-container', {
               opacity: 1,
               y: 0,
               duration: 0.8,
               ease: 'power3.out',
               scrollTrigger: {
                  trigger: '.filter-container',
                  start: 'top 85%',
                  toggleActions: 'play none none none'
               }
            });
            
            // Product boxes with staggered animation
            gsap.utils.toArray('.box').forEach((box, i) => {
               const badges = box.querySelectorAll('.discount-badge, .stock-status');
               const detailsBtn = box.querySelector('.details-btn');
               
               // Create a timeline for each box
               const boxTl = gsap.timeline({
                  scrollTrigger: {
                     trigger: box,
                     start: 'top 90%',
                     toggleActions: 'play none none none'
                  }
               });
               
               // Animate the box
               boxTl.to(box, {
                  opacity: 1,
                  y: 0,
                  rotateX: 0,
                  duration: 1,
                  delay: i * 0.1,
                  ease: 'power3.out'
               });
               
               // Animate badges
               boxTl.to(badges, {
                  x: 0,
                  opacity: 1,
                  duration: 0.6,
                  stagger: 0.1,
                  ease: 'power3.out'
               }, '-=0.6');
               
               // Animate details button
               boxTl.to(detailsBtn, {
                  y: 0,
                  opacity: 1,
                  duration: 0.6,
                  ease: 'power3.out'
               }, '-=0.4');
            });
         }
         
         // Scroll Down Button
         document.getElementById('scroll-down').addEventListener('click', function() {
            gsap.to(window, {
               duration: 1.2, 
               scrollTo: {
                  y: '#collection',
                  offsetY: 50
               },
               ease: 'power3.inOut'
            });
         });
         
         // Quantity buttons functionality with GSAP animations
         document.querySelectorAll('.qty-decrease').forEach(btn => {
            btn.addEventListener('click', function() {
               const input = this.parentElement.querySelector('input');
               const value = parseInt(input.value);
               if (value > 1) {
                  gsap.to(input, {
                     keyframes: [
                        { scale: 0.9, duration: 0.1 },
                        { scale: 1, duration: 0.1 }
                     ]
                  });
                  input.value = value - 1;
               }
            });
         });
         
         document.querySelectorAll('.qty-increase').forEach(btn => {
            btn.addEventListener('click', function() {
               const input = this.parentElement.querySelector('input');
               const value = parseInt(input.value);
               gsap.to(input, {
                  keyframes: [
                     { scale: 0.9, duration: 0.1 },
                     { scale: 1, duration: 0.1 }
                  ]
               });
               input.value = value + 1;
            });
         });
         
         // Add to cart animation
         document.querySelectorAll('.cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
               // Show notification
               gsap.to('#cart-notification', {
                  x: 0,
                  opacity: 1,
                  duration: 0.5,
                  ease: 'power3.out'
               });
               
               // Hide notification after 3 seconds
               gsap.to('#cart-notification', {
                  x: 'calc(100% + 30px)',
                  opacity: 0,
                  duration: 0.5,
                  ease: 'power3.in',
                  delay: 3
               });
            });
         });
         
         // Enhanced Three.js - Hero Canvas
         const heroCanvas = document.getElementById('hero-canvas');
         const heroScene = new THREE.Scene();
         const heroCamera = new THREE.PerspectiveCamera(75, window.innerWidth / heroCanvas.clientHeight, 0.1, 1000);
         const heroRenderer = new THREE.WebGLRenderer({ 
            alpha: true, 
            antialias: true,
            powerPreference: 'high-performance'
         });
         
         heroRenderer.setSize(window.innerWidth, heroCanvas.clientHeight);
         heroRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
         heroCanvas.appendChild(heroRenderer.domElement);
         
         // Create interactive particles with varying sizes
         const particlesGeometry = new THREE.BufferGeometry();
         const particlesCount = 1000;
         
         const posArray = new Float32Array(particlesCount * 3);
         const scaleArray = new Float32Array(particlesCount);
         
         for(let i = 0; i < particlesCount * 3; i += 3) {
            posArray[i] = (Math.random() - 0.5) * 15;
            posArray[i+1] = (Math.random() - 0.5) * 15;
            posArray[i+2] = (Math.random() - 0.5) * 15;
            
            scaleArray[i/3] = Math.random();
         }
         
         particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
         particlesGeometry.setAttribute('scale', new THREE.BufferAttribute(scaleArray, 1));
         
         const particlesMaterial = new THREE.PointsMaterial({
            size: 0.05,
            color: 0xd4af37,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending,
            sizeAttenuation: true
         });
         
         const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
         heroScene.add(particlesMesh);
         
         heroCamera.position.z = 5;
         
         // Mouse interaction
         let mouseX = 0;
         let mouseY = 0;
         let targetX = 0;
         let targetY = 0;
         
         document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX / window.innerWidth) * 2 - 1;
            mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
         });
         
         // Animation
         const animateHero = function() {
            requestAnimationFrame(animateHero);
            
            targetX = mouseX * 0.2;
            targetY = mouseY * 0.2;
            
            particlesMesh.rotation.x += 0.0005;
            particlesMesh.rotation.y += 0.0005;
            
            particlesMesh.rotation.x += (targetY - particlesMesh.rotation.x) * 0.02;
            particlesMesh.rotation.y += (targetX - particlesMesh.rotation.y) * 0.02;
            
            heroRenderer.render(heroScene, heroCamera);
         };
         
         animateHero();
         
         // Responsive
         window.addEventListener('resize', () => {
            const newWidth = window.innerWidth;
            const newHeight = heroCanvas.clientHeight;
            
            heroCamera.aspect = newWidth / newHeight;
            heroCamera.updateProjectionMatrix();
            heroRenderer.setSize(newWidth, newHeight);
            heroRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
         });
         
         /* ======= Search and Filter Functionality ======= */
         
         // Elements
         const searchForm = document.getElementById('search-form');
         const searchInput = document.getElementById('search-input');
         const searchResults = document.getElementById('search-results');
         const searchQuery = document.getElementById('search-query');
         const searchResultsCount = document.getElementById('search-results-count');
         const productsContainer = document.getElementById('products-container');
         const noResults = document.getElementById('no-results');
         const resetFiltersBtn = document.getElementById('reset-filters');
         const filterBtns = document.querySelectorAll('.filter-btn');
         
         // Variables to track current filter state
         let currentCategory = 'all';
         let currentSearch = '';
         
         // Function to filter products
         function filterProducts() {
            const products = document.querySelectorAll('.box');
            let visibleCount = 0;
            
            products.forEach(product => {
               const productCategory = product.dataset.category;
               const productName = product.dataset.name;
               const productContent = product.textContent.toLowerCase();
               
               // Check if product matches both category and search filters
               const matchesCategory = currentCategory === 'all' || productCategory === currentCategory;
               const matchesSearch = !currentSearch || 
                                    productName.includes(currentSearch) || 
                                    productContent.includes(currentSearch);
               
               if (matchesCategory && matchesSearch) {
                  gsap.to(product, {
                     display: 'flex',
                     opacity: 1,
                     y: 0,
                     duration: 0.5,
                     ease: 'power2.out'
                  });
                  visibleCount++;
               } else {
                  gsap.to(product, {
                     opacity: 0,
                     y: 20,
                     duration: 0.3,
                     ease: 'power2.in',
                     onComplete: () => {
                        product.style.display = 'none';
                     }
                  });
               }
            });
            
            // Update search results
            if (currentSearch) {
               searchQuery.textContent = currentSearch;
               searchResultsCount.textContent = visibleCount;
               searchResults.classList.add('active');
            } else {
               searchResults.classList.remove('active');
            }
            
            // Show/hide no results message
            if (visibleCount === 0) {
               gsap.to(noResults, {
                  display: 'block',
                  opacity: 1,
                  y: 0,
                  duration: 0.5,
                  ease: 'power2.out'
               });
            } else {
               gsap.to(noResults, {
                  opacity: 0,
                  y: 20,
                  duration: 0.3,
                  ease: 'power2.in',
                  onComplete: () => {
                     noResults.style.display = 'none';
                  }
               });
            }
         }
         
         // Handle search form submission
         searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            currentSearch = searchInput.value.trim().toLowerCase();
            filterProducts();
         });
         
         // Handle search input real-time filtering (optional)
         searchInput.addEventListener('input', function() {
            if (this.value.length >= 3 || this.value.length === 0) {
               currentSearch = this.value.trim().toLowerCase();
               filterProducts();
            }
         });
         
         // Handle category filter buttons
         filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
               // Update UI
               filterBtns.forEach(b => b.classList.remove('active'));
               this.classList.add('active');
               
               // Update filter state
               currentCategory = this.dataset.category;
               
               // Apply filters
               filterProducts();
            });
         });
         
         // Reset all filters
         resetFiltersBtn.addEventListener('click', function() {
            // Reset search
            searchInput.value = '';
            currentSearch = '';
            searchResults.classList.remove('active');
            
            // Reset category
            filterBtns.forEach(btn => btn.classList.remove('active'));
            document.querySelector('.filter-btn[data-category="all"]').classList.add('active');
            currentCategory = 'all';
            
            // Apply reset
            filterProducts();
            
            // Scroll to products
            gsap.to(window, {
               duration: 0.8, 
               scrollTo: {
                  y: '#collection',
                  offsetY: 50
               },
               ease: 'power2.inOut'
            });
         });
      });
   </script>
</body>
</html>