<?php

include 'config.php';
session_start();


if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit();
}

$user_id = $_SESSION['user_id'];
$message = [];


if(isset($_POST['update_cart'])){
   $cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_SANITIZE_NUMBER_INT);
   $cart_quantity = filter_input(INPUT_POST, 'cart_quantity', FILTER_SANITIZE_NUMBER_INT);
   
   if($cart_id && $cart_quantity > 0){
      $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
      $stmt->bind_param("iii", $cart_quantity, $cart_id, $user_id);
      
      if($stmt->execute()){
         $message[] = ['type' => 'success', 'text' => 'Cart updated successfully!'];
      } else {
         $message[] = ['type' => 'error', 'text' => 'Failed to update cart.'];
      }
      $stmt->close();
   }
}


if(isset($_GET['delete'])){
   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);
   
   if($delete_id){
      $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
      $stmt->bind_param("ii", $delete_id, $user_id);
      $stmt->execute();
      $stmt->close();
      header('location:cart.php');
      exit();
   }
}

// Clear entire cart
if(isset($_GET['delete_all'])){
   $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
   $stmt->bind_param("i", $user_id);
   $stmt->execute();
   $stmt->close();
   header('location:cart.php');
   exit();
}

// FIX: Store messages for this page but pass empty array to header.php
$cart_messages = $message; // Save messages for cart page
$message = []; // Pass empty array to header.php instead of string
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Shopping Cart | BookCraft</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts - Poppins -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- GSAP for animations -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
   
   <!-- Three.js for 3D elements -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.155.0/three.min.js"></script>
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      /* Modern Cart Styles with Enhanced UI */
      :root {
         --primary: #4a6eb5;
         --primary-dark: #2c4f8b;
         --secondary: #e63946;
         --success: #28a745;
         --error: #dc3545;
         --light: #f8f9fa;
         --dark: #343a40;
         --gray: #6c757d;
         --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
         --shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.1);
         --border-radius: 12px;
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }
      
      body {
         font-family: 'Poppins', sans-serif;
         color: var(--dark);
         background-color: #f9f9f9;
         overflow-x: hidden;
         position: relative;
      }
      
      /* Three.js canvas */
      #bg-canvas {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         z-index: -1;
         opacity: 0.5;
      }
      
      .shopping-cart {
         max-width: 1200px;
         margin: 0 auto;
         padding: 2rem;
         position: relative;
         z-index: 1;
      }
      
      .cart-header {
         margin-bottom: 2rem;
         text-align: center;
         opacity: 0; /* For GSAP animation */
      }
      
      .cart-header h1 {
         font-size: 2.8rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 0.5rem;
         background: linear-gradient(to right, var(--primary), var(--primary-dark));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         background-clip: text;
         text-fill-color: transparent;
      }
      
      .cart-breadcrumb {
         display: flex;
         justify-content: center;
         align-items: center;
         gap: 0.5rem;
         color: var(--gray);
         font-size: 1rem;
      }
      
      .cart-breadcrumb a {
         color: var(--primary);
         transition: color 0.3s, transform 0.3s;
         text-decoration: none;
      }
      
      .cart-breadcrumb a:hover {
         color: var(--primary-dark);
         transform: translateY(-2px);
      }
      
      .notification {
         padding: 1rem 1.5rem;
         margin-bottom: 2rem;
         border-radius: var(--border-radius);
         display: flex;
         align-items: center;
         box-shadow: var(--shadow);
         transform: translateY(20px);
         opacity: 0;
      }
      
      .notification.success {
         background: linear-gradient(to right, #e7f7ed, #f0fff4);
         border-left: 4px solid var(--success);
         color: #155724;
      }
      
      .notification.error {
         background: linear-gradient(to right, #f8d7da, #ffebee);
         border-left: 4px solid var(--error);
         color: #721c24;
      }
      
      .notification i {
         margin-right: 10px;
         font-size: 1.2rem;
         animation: pulse 1.5s infinite;
      }
      
      @keyframes pulse {
         0% { transform: scale(1); }
         50% { transform: scale(1.1); }
         100% { transform: scale(1); }
      }
      
      .cart-items {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
         gap: 2rem;
         margin-bottom: 2rem;
      }
      
      .cart-item {
         background-color: #fff;
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         overflow: hidden;
         transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
                    box-shadow 0.4s ease;
         position: relative;
         opacity: 0;
         transform: translateY(30px);
      }
      
      .cart-item:hover {
         transform: translateY(-8px) scale(1.02);
         box-shadow: var(--shadow-hover);
      }
      
      .cart-item .delete-btn {
         position: absolute;
         top: 10px;
         right: 10px;
         background-color: rgba(255, 255, 255, 0.95);
         color: var(--error);
         width: 36px;
         height: 36px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
         z-index: 2;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }
      
      .cart-item .delete-btn:hover {
         background-color: var(--error);
         color: white;
         transform: rotate(90deg);
         box-shadow: 0 6px 12px rgba(220, 53, 69, 0.2);
      }
      
      .cart-item img {
         width: 100%;
         height: 220px;
         object-fit: cover;
         border-bottom: 1px solid #f0f0f0;
         transition: transform 0.5s ease;
      }
      
      .cart-item:hover img {
         transform: scale(1.05);
      }
      
      .cart-item-content {
         padding: 1.5rem;
      }
      
      .cart-item-name {
         font-size: 1.25rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 0.5rem;
         transition: color 0.3s;
      }
      
      .cart-item:hover .cart-item-name {
         color: var(--primary);
      }
      
      .cart-item-price {
         color: var(--secondary);
         font-size: 1.2rem;
         font-weight: 700;
         margin-bottom: 1rem;
         display: flex;
         align-items: center;
      }
      
      .cart-item-price::before {
         content: '$';
         font-size: 0.8em;
         margin-right: 2px;
         opacity: 0.8;
      }
      
      .quantity-control {
         display: flex;
         align-items: center;
         margin-bottom: 1rem;
         background: #f8f9fa;
         padding: 0.5rem;
         border-radius: 50px;
         box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
      }
      
      .quantity-btn {
         background: linear-gradient(to bottom, #ffffff, #f8f9fa);
         border: 1px solid #eee;
         color: var(--dark);
         width: 35px;
         height: 35px;
         font-size: 1.2rem;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.3s;
         border-radius: 50%;
         box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      }
      
      .quantity-btn:hover {
         background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
         transform: translateY(-2px);
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }
      
      .quantity-btn:active {
         transform: translateY(0);
         box-shadow: 0 2px 3px rgba(0,0,0,0.1);
      }
      
      .quantity-input {
         width: 60px;
         height: 35px;
         border: none;
         text-align: center;
         font-size: 1rem;
         margin: 0 0.5rem;
         font-weight: 600;
         color: var(--dark);
         background-color: transparent;
         font-family: 'Poppins', sans-serif;
      }
      
      .quantity-input:focus {
         outline: none;
      }
      
      .update-btn {
         background: linear-gradient(to right, var(--primary), var(--primary-dark));
         color: white;
         border: none;
         padding: 0.6rem 1.2rem;
         border-radius: 50px;
         cursor: pointer;
         transition: all 0.3s;
         margin-left: 10px;
         font-weight: 500;
         box-shadow: 0 4px 10px rgba(74, 110, 181, 0.2);
      }
      
      .update-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 6px 15px rgba(74, 110, 181, 0.3);
         background: linear-gradient(to right, var(--primary-dark), var(--primary));
      }
      
      .update-btn:active {
         transform: translateY(-1px);
         box-shadow: 0 2px 8px rgba(74, 110, 181, 0.3);
      }
      
      .cart-item-subtotal {
         font-size: 1.1rem;
         color: var(--dark);
         margin-top: 1rem;
         padding-top: 1rem;
         border-top: 1px dashed #eee;
         display: flex;
         justify-content: space-between;
      }
      
      .cart-item-subtotal span {
         font-weight: 700;
         color: var(--success);
         background: linear-gradient(120deg, #28a745, #20c997);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         background-clip: text;
      }
      
      .empty-cart {
         text-align: center;
         padding: 4rem 1rem;
         background-color: white;
         border-radius: var(--border-radius);
         box-shadow: var(--shadow);
         transform: translateY(30px);
         opacity: 0;
      }
      
      .empty-cart i {
         font-size: 5rem;
         color: #e9ecef;
         margin-bottom: 1.5rem;
         display: block;
      }
      
      .empty-cart p {
         font-size: 1.4rem;
         color: var(--gray);
         margin-bottom: 2.5rem;
         font-weight: 300;
      }
      
      .empty-cart .shop-now-btn {
         background: linear-gradient(to right, var(--primary), var(--primary-dark));
         color: white;
         padding: 0.9rem 2rem;
         border-radius: 50px;
         display: inline-block;
         text-decoration: none;
         transition: all 0.3s ease;
         font-weight: 500;
         box-shadow: 0 4px 15px rgba(74, 110, 181, 0.3);
         position: relative;
         overflow: hidden;
      }
      
      .empty-cart .shop-now-btn::after {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(255, 255, 255, 0.2);
         transform: translateX(-100%) rotate(45deg);
         transition: transform 0.6s;
      }
      
      .empty-cart .shop-now-btn:hover::after {
         transform: translateX(100%) rotate(45deg);
      }
      
      .empty-cart .shop-now-btn:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 20px rgba(74, 110, 181, 0.4);
      }
      
      .cart-actions {
         margin: 2rem 0;
         text-align: center;
         opacity: 0;
         transform: translateY(20px);
      }
      
      .cart-actions .clear-cart {
         background: linear-gradient(to right, var(--error), #f56565);
         color: white;
         border: none;
         padding: 0.9rem 2rem;
         border-radius: 50px;
         text-decoration: none;
         display: inline-block;
         transition: all 0.3s;
         font-weight: 500;
         box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
      }
      
      .cart-actions .clear-cart:hover {
         transform: translateY(-3px);
         box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
      }
      
      .cart-actions .clear-cart.disabled {
         background: linear-gradient(to right, #f8d7da, #e6bdc2);
         color: #721c24;
         cursor: not-allowed;
         box-shadow: none;
      }
      
      .cart-summary {
         background: white;
         border-radius: var(--border-radius);
         padding: 2rem;
         margin-top: 2rem;
         box-shadow: var(--shadow);
         position: relative;
         overflow: hidden;
         opacity: 0;
         transform: translateY(20px);
      }
      
      .cart-summary::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 5px;
         background: linear-gradient(to right, var(--primary), var(--primary-dark), var(--secondary));
      }
      
      .cart-total {
         font-size: 1.6rem;
         color: var(--dark);
         margin-bottom: 2rem;
         text-align: center;
      }
      
      .cart-total span {
         font-weight: 700;
         color: var(--secondary);
         font-size: 2rem;
      }
      
      .cart-buttons {
         display: flex;
         justify-content: space-between;
         gap: 1.5rem;
      }
      
      .continue-shopping {
         background: linear-gradient(to right, var(--gray), #5a6268);
         color: white;
         text-decoration: none;
         padding: 0.9rem 1.5rem;
         border-radius: 50px;
         flex: 1;
         text-align: center;
         transition: all 0.3s;
         font-weight: 500;
         box-shadow: 0 4px 10px rgba(108, 117, 125, 0.2);
         position: relative;
         overflow: hidden;
      }
      
      .continue-shopping::after {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(255, 255, 255, 0.1);
         transform: translateX(-100%) rotate(45deg);
         transition: transform 0.6s;
      }
      
      .continue-shopping:hover::after {
         transform: translateX(100%) rotate(45deg);
      }
      
      .continue-shopping:hover {
         transform: translateY(-3px);
         box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3);
      }
      
      .checkout-btn {
         background: linear-gradient(to right, var(--success), #20c997);
         color: white;
         text-decoration: none;
         padding: 0.9rem 1.5rem;
         border-radius: 50px;
         flex: 1;
         text-align: center;
         transition: all 0.3s ease;
         font-weight: 500;
         box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
         position: relative;
         overflow: hidden;
      }
      
      .checkout-btn::after {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(255, 255, 255, 0.1);
         transform: translateX(-100%) rotate(45deg);
         transition: transform 0.6s;
      }
      
      .checkout-btn:hover::after {
         transform: translateX(100%) rotate(45deg);
      }
      
      .checkout-btn:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
      }
      
      .checkout-btn.disabled {
         background: linear-gradient(to right, #e9ecef, #dee2e6);
         color: var(--gray);
         cursor: not-allowed;
         transform: none;
         box-shadow: none;
      }
      
      .checkout-btn.disabled:hover {
         transform: none;
         box-shadow: none;
      }
      
      .checkout-btn.disabled::after {
         display: none;
      }
      
      /* Responsive adjustments */
      @media (max-width: 768px) {
         .cart-buttons {
            flex-direction: column;
         }
         
         .cart-items {
            grid-template-columns: 1fr;
         }
         
         .cart-header h1 {
            font-size: 2rem;
         }
         
         .cart-total {
            font-size: 1.4rem;
         }
         
         .cart-total span {
            font-size: 1.6rem;
         }
      }
   </style>
</head>
<body>
   
<!-- Three.js background canvas -->
<canvas id="bg-canvas"></canvas>

<?php 
// Include header.php with empty message array
include 'header.php';
// Now restore our cart messages for display in this page
$message = $cart_messages;
?>

<section class="shopping-cart">
   <div class="cart-header">
      <h1>Your Shopping Cart</h1>
      <div class="cart-breadcrumb">
         <a href="home.php">Home</a>
         <i class="fas fa-angle-right"></i>
         <span>Shopping Cart</span>
      </div>
   </div>
   
   <?php if(!empty($message)): ?>
      <?php foreach($message as $msg): ?>
         <div class="notification <?php echo $msg['type']; ?>">
            <i class="fas fa-<?php echo $msg['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $msg['text']; ?>
         </div>
      <?php endforeach; ?>
   <?php endif; ?>

   <?php
   $grand_total = 0;
   $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $stmt->bind_param("i", $user_id);
   $stmt->execute();
   $result = $stmt->get_result();
   
   if($result->num_rows > 0):
   ?>
   
   <div class="cart-items">
      <?php $item_index = 0; while($item = $result->fetch_assoc()): $item_index++; ?>
         <div class="cart-item" data-index="<?php echo $item_index; ?>">
            <a href="cart.php?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="animateRemoval(this, event);">
               <i class="fas fa-times"></i>
            </a>
            <img src="uploaded_img/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
            <div class="cart-item-content">
               <div class="cart-item-name"><?php echo $item['name']; ?></div>
               <div class="cart-item-price"><?php echo number_format($item['price'], 2); ?></div>
               
               <form action="" method="post" class="quantity-form">
                  <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                  <div class="quantity-control">
                     <button type="button" class="quantity-btn decrease-btn" onclick="decreaseQuantity(this)">
                        <i class="fas fa-minus"></i>
                     </button>
                     <input type="number" name="cart_quantity" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                     <button type="button" class="quantity-btn increase-btn" onclick="increaseQuantity(this)">
                        <i class="fas fa-plus"></i>
                     </button>
                     <button type="submit" name="update_cart" class="update-btn">
                        <i class="fas fa-sync-alt"></i> Update
                     </button>
                  </div>
               </form>
               
               <div class="cart-item-subtotal">
                  Subtotal: <span>Rs<?php echo number_format($sub_total = ($item['quantity'] * $item['price']), 2); ?></span>
               </div>
            </div>
         </div>
         <?php $grand_total += $sub_total; ?>
      <?php endwhile; ?>
   </div>
   
   <div class="cart-actions">
      <a href="cart.php?delete_all" class="clear-cart <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirmClearCart(event);">
         <i class="fas fa-trash"></i> Clear Cart
      </a>
   </div>
   
   <div class="cart-summary">
      <div class="cart-total">
         Total: <span>Rs<?php echo number_format($grand_total, 2); ?></span>
      </div>
      <div class="cart-buttons">
         <a href="shop.php" class="continue-shopping">
            <i class="fas fa-arrow-left"></i> Continue Shopping
         </a>
         <a href="checkout.php" class="checkout-btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="<?php echo ($grand_total > 0) ? 'animateCheckout(this)' : 'alert(\'Your cart is empty. Add items before checkout.\'); return false;'; ?>">
            Proceed to Checkout <i class="fas fa-arrow-right"></i>
         </a>
      </div>
   </div>
   
   <?php else: ?>
   
   <div class="empty-cart">
      <i class="fas fa-shopping-cart"></i>
      <p>Your cart is empty</p>
      <a href="shop.php" class="shop-now-btn">
         Shop Now <i class="fas fa-shopping-bag"></i>
      </a>
   </div>
   
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

<script>
   // Initialize Three.js background
   function initThreeJsBackground() {
      // Create scene
      const scene = new THREE.Scene();
      
      // Camera setup
      const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
      camera.position.z = 5;
      
      // Get canvas and set renderer
      const canvas = document.getElementById('bg-canvas');
      const renderer = new THREE.WebGLRenderer({
         canvas: canvas,
         antialias: true,
         alpha: true
      });
      renderer.setSize(window.innerWidth, window.innerHeight);
      renderer.setPixelRatio(window.devicePixelRatio);
      
      // Create floating particles for background
      const particleGeometry = new THREE.BufferGeometry();
      const particleCount = 200;
      
      const posArray = new Float32Array(particleCount * 3);
      const scaleArray = new Float32Array(particleCount);
      
      for(let i = 0; i < particleCount * 3; i += 3) {
         // Position particles in a sphere
         const x = (Math.random() - 0.5) * 15;
         const y = (Math.random() - 0.5) * 15;
         const z = (Math.random() - 0.5) * 15;
         
         posArray[i] = x;
         posArray[i+1] = y;
         posArray[i+2] = z;
         
         // Random scale for each particle
         scaleArray[i/3] = Math.random() * 5 + 1;
      }
      
      particleGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
      particleGeometry.setAttribute('scale', new THREE.BufferAttribute(scaleArray, 1));
      
      // Create a custom shader material
      const particleMaterial = new THREE.ShaderMaterial({
         uniforms: {
            time: { value: 0 },
            color: { value: new THREE.Color('#4a6eb5') }
         },
         vertexShader: `
            attribute float scale;
            uniform float time;
            
            void main() {
               vec3 pos = position;
               pos.y += sin(time * 0.2 + position.x) * 0.2;
               pos.x += sin(time * 0.3 + position.y) * 0.2;
               
               vec4 mvPosition = modelViewMatrix * vec4(pos, 1.0);
               gl_PointSize = scale * (300.0 / -mvPosition.z);
               gl_Position = projectionMatrix * mvPosition;
            }
         `,
         fragmentShader: `
            uniform vec3 color;
            
            void main() {
               // Create a smooth circle
               float dist = length(gl_PointCoord - vec2(0.5));
               if (dist > 0.5) discard;
               
               // Add some gradient to the point
               float alpha = smoothstep(0.5, 0.0, dist);
               gl_FragColor = vec4(color, alpha * 0.6);
            }
         `,
         transparent: true,
         blending: THREE.AdditiveBlending,
         depthTest: false
      });
      
      const particleSystem = new THREE.Points(particleGeometry, particleMaterial);
      scene.add(particleSystem);
      
      // Handle window resize
      window.addEventListener('resize', () => {
         camera.aspect = window.innerWidth / window.innerHeight;
         camera.updateProjectionMatrix();
         renderer.setSize(window.innerWidth, window.innerHeight);
      });
      
      // Animation loop
      function animate() {
         requestAnimationFrame(animate);
         
         // Update time uniform
         particleMaterial.uniforms.time.value += 0.01;
         
         // Rotate particle system
         particleSystem.rotation.y += 0.001;
         particleSystem.rotation.x += 0.0005;
         
         renderer.render(scene, camera);
      }
      
      animate();
   }
   
   // Quantity control functions with GSAP animation
   function decreaseQuantity(button) {
      const container = button.closest('.quantity-control');
      const input = container.querySelector('.quantity-input');
      let value = parseInt(input.value);
      if (value > 1) {
         gsap.to(button, {
            scale: 0.8,
            duration: 0.1,
            onComplete: function() {
               gsap.to(button, {
                  scale: 1,
                  duration: 0.1
               });
               input.value = value - 1;
               updateSubtotal(input);
            }
         });
      }
   }
   
   function increaseQuantity(button) {
      const container = button.closest('.quantity-control');
      const input = container.querySelector('.quantity-input');
      let value = parseInt(input.value);
      if (value < 99) {
         gsap.to(button, {
            scale: 0.8,
            duration: 0.1,
            onComplete: function() {
               gsap.to(button, {
                  scale: 1,
                  duration: 0.1
               });
               input.value = value + 1;
               updateSubtotal(input);
            }
         });
      }
   }
   
   // Calculate subtotal without refreshing page
   function updateSubtotal(input) {
      const item = input.closest('.cart-item');
      const price = parseFloat(item.querySelector('.cart-item-price').textContent);
      const quantity = parseInt(input.value);
      const subtotalElement = item.querySelector('.cart-item-subtotal span');
      
      const subtotal = price * quantity;
      
      // FIXED: Proper string formatting with backticks
      gsap.to(subtotalElement, {
         innerText: `$${subtotal.toFixed(2)}`,
         duration: 0.5,
         snap: { innerText: 0.01 }
      });
   }
   
   // Animate removal of item before form submission
   function animateRemoval(button, e) {
      e.preventDefault();
      
      if(!confirm('Are you sure you want to remove this item?')) {
         return false;
      }
      
      const item = button.closest('.cart-item');
      const href = button.getAttribute('href');
      
      gsap.to(item, {
         scale: 0.8,
         opacity: 0,
         y: -20,
         duration: 0.5,
         ease: "power2.out",
         onComplete: function() {
            window.location.href = href;
         }
      });
   }
   
   // Confirm clear cart with animation
   function confirmClearCart(e) {
      if (e.target.classList.contains('disabled')) {
         e.preventDefault();
         return false;
      }
      
      if(!confirm('Are you sure you want to empty your cart?')) {
         e.preventDefault();
         return false;
      }
      
      const cartItems = document.querySelectorAll('.cart-item');
      
      gsap.to(cartItems, {
         scale: 0.8,
         opacity: 0,
         y: -20,
         stagger: 0.1,
         duration: 0.5,
         ease: "power2.out"
      });
      
      return true;
   }
   
   // Animate checkout button
   function animateCheckout(button) {
      gsap.to(button, {
         scale: 1.05,
         duration: 0.2,
         yoyo: true,
         repeat: 1,
         onComplete: function() {
            window.location.href = button.getAttribute('href');
         }
      });
      
      return false;
   }
   
   // Initialize GSAP animations
   function initAnimations() {
      // Header animation
      gsap.to('.cart-header', {
         opacity: 1,
         y: 0,
         duration: 1,
         ease: 'power3.out'
      });
      
      // Notification animation
      gsap.to('.notification', {
         opacity: 1,
         y: 0,
         duration: 0.8,
         ease: 'back.out',
         delay: 0.5
      });
      
      // Empty cart animation
      gsap.to('.empty-cart', {
         opacity: 1,
         y: 0,
         duration: 0.8,
         ease: 'back.out',
         delay: 0.5
      });
      
      // Cart items animation
      const cartItems = document.querySelectorAll('.cart-item');
      gsap.to(cartItems, {
         opacity: 1,
         y: 0,
         stagger: 0.15,
         duration: 0.8,
         ease: 'back.out',
         delay: 0.7
      });
      
      // Cart actions animation
      gsap.to('.cart-actions', {
         opacity: 1,
         y: 0,
         duration: 0.8,
         ease: 'power3.out',
         delay: 1 + (cartItems.length * 0.1)
      });
      
      // Cart summary animation
      gsap.to('.cart-summary', {
         opacity: 1,
         y: 0,
         duration: 0.8,
         ease: 'power3.out',
         delay: 1.2 + (cartItems.length * 0.1)
      });
   }
   
   document.addEventListener('DOMContentLoaded', function() {
      // Initialize Three.js background
      initThreeJsBackground();
      
      // Initialize GSAP animations
      initAnimations();
      
      // Quantity input listeners for direct input
      const quantityInputs = document.querySelectorAll('.quantity-input');
      quantityInputs.forEach(input => {
         input.addEventListener('change', function() {
            if(this.value < 1) this.value = 1;
            if(this.value > 99) this.value = 99;
            updateSubtotal(this);
         });
      });
      
      // Disable checkout if cart is empty
      const checkoutBtn = document.querySelector('.checkout-btn.disabled');
      if (checkoutBtn) {
         checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            gsap.to(checkoutBtn, {
               x: [-5, 5, -5, 5, 0],
               duration: 0.4,
               onComplete: function() {
                  alert('Your cart is empty. Add items before checkout.');
               }
            });
         });
      }
   });
</script>

</body>
</html>