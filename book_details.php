<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($product_id <= 0) {
    header('location:shop.php');
    exit();
}

// Add to cart functionality
if(isset($_POST['add_to_cart'])){
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'Already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO cart(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'Product added to cart!';
   }
}

// Fetch product details
$select_product = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'") or die('query failed');

if(mysqli_num_rows($select_product) == 0) {
    header('location:shop.php');
    exit();
}

$product = mysqli_fetch_assoc($select_product);

// Fetch related products (other books)
$related_products = mysqli_query($conn, "SELECT * FROM products WHERE id != '$product_id' ORDER BY RAND() LIMIT 4") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo htmlspecialchars($product['name']); ?> | BookCraft</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
   
   <!-- GSAP and Plugins -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   
   <!-- Custom styles for book details -->
   <style>
      .book-details {
         padding: 120px 0 80px;
         min-height: 100vh;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         position: relative;
         overflow: hidden;
      }
      
      .book-details::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         bottom: 0;
         background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="90" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
         animation: float 20s infinite linear;
      }
      
      @keyframes float {
         0% { transform: translateY(0) rotate(0deg); }
         100% { transform: translateY(-100vh) rotate(360deg); }
      }
      
      .book-details .container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 20px;
         position: relative;
         z-index: 1;
      }
      
      .breadcrumb {
         display: flex;
         align-items: center;
         gap: 10px;
         margin-bottom: 30px;
         color: rgba(255, 255, 255, 0.8);
         font-size: 14px;
      }
      
      .breadcrumb a {
         color: rgba(255, 255, 255, 0.8);
         text-decoration: none;
         transition: color 0.3s ease;
      }
      
      .breadcrumb a:hover {
         color: #ffffff;
      }
      
      .product-detail-card {
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(10px);
         border-radius: 20px;
         padding: 40px;
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
         margin-bottom: 40px;
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 40px;
         align-items: center;
      }
      
      .product-image {
         text-align: center;
         position: relative;
      }
      
      .product-image img {
         width: 100%;
         max-width: 350px;
         height: auto;
         border-radius: 15px;
         box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
         transition: transform 0.3s ease;
      }
      
      .product-image:hover img {
         transform: scale(1.05);
      }
      
      .product-info h1 {
         font-family: 'Playfair Display', serif;
         font-size: 2.5rem;
         color: #2c3e50;
         margin-bottom: 20px;
         font-weight: 700;
      }
      
      .product-price {
         font-size: 2rem;
         color: #e74c3c;
         font-weight: 600;
         margin-bottom: 20px;
      }
      
      .product-price span {
         font-size: 1.2rem;
         color: #7f8c8d;
      }
      
      .product-description {
         color: #555;
         line-height: 1.8;
         margin-bottom: 30px;
         font-size: 1.1rem;
      }
      
      .product-actions {
         display: flex;
         gap: 20px;
         align-items: center;
         flex-wrap: wrap;
      }
      
      .quantity-selector {
         display: flex;
         align-items: center;
         gap: 10px;
         background: #f8f9fa;
         border-radius: 10px;
         padding: 10px 15px;
         border: 2px solid #e9ecef;
      }
      
      .quantity-selector label {
         font-weight: 600;
         color: #495057;
      }
      
      .quantity-selector input {
         width: 60px;
         text-align: center;
         border: none;
         background: transparent;
         font-size: 16px;
         font-weight: 600;
      }
      
      .btn-add-to-cart {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         border: none;
         padding: 15px 30px;
         border-radius: 10px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         gap: 10px;
         min-width: 180px;
         justify-content: center;
      }
      
      .btn-add-to-cart:hover {
         transform: translateY(-2px);
         box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      }
      
      .btn-back {
         background: #6c757d;
         color: white;
         border: none;
         padding: 12px 25px;
         border-radius: 10px;
         font-size: 1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         gap: 10px;
         text-decoration: none;
      }
      
      .btn-back:hover {
         background: #5a6268;
         transform: translateY(-2px);
      }
      
      .related-products {
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(10px);
         border-radius: 20px;
         padding: 40px;
         box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      }
      
      .related-products h2 {
         font-family: 'Playfair Display', serif;
         font-size: 2rem;
         color: #2c3e50;
         margin-bottom: 30px;
         text-align: center;
      }
      
      .related-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 30px;
      }
      
      .related-item {
         background: white;
         border-radius: 15px;
         padding: 20px;
         box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
         transition: transform 0.3s ease;
         text-align: center;
      }
      
      .related-item:hover {
         transform: translateY(-5px);
      }
      
      .related-item img {
         width: 100%;
         height: 200px;
         object-fit: cover;
         border-radius: 10px;
         margin-bottom: 15px;
      }
      
      .related-item h3 {
         font-size: 1.1rem;
         color: #2c3e50;
         margin-bottom: 10px;
      }
      
      .related-item .price {
         color: #e74c3c;
         font-weight: 600;
         font-size: 1.2rem;
         margin-bottom: 15px;
      }
      
      .related-item .btn-view {
         background: #17a2b8;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 8px;
         font-size: 0.9rem;
         font-weight: 500;
         cursor: pointer;
         transition: all 0.3s ease;
         text-decoration: none;
         display: inline-block;
      }
      
      .related-item .btn-view:hover {
         background: #138496;
         transform: translateY(-2px);
      }
      
      .product-features {
         margin-top: 30px;
         padding-top: 30px;
         border-top: 1px solid #e9ecef;
      }
      
      .features-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 20px;
         margin-top: 20px;
      }
      
      .feature-item {
         display: flex;
         align-items: center;
         gap: 10px;
         color: #555;
      }
      
      .feature-item i {
         color: #667eea;
         font-size: 1.2rem;
      }
      
      @media (max-width: 768px) {
         .product-detail-card {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 30px 20px;
         }
         
         .product-info h1 {
            font-size: 2rem;
         }
         
         .product-actions {
            flex-direction: column;
            align-items: stretch;
         }
         
         .btn-add-to-cart {
            width: 100%;
         }
         
         .related-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         }
      }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="book-details">
   <div class="container">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
         <a href="home.php"><i class="fas fa-home"></i> Home</a>
         <i class="fas fa-angle-right"></i>
         <a href="shop.php">Shop</a>
         <i class="fas fa-angle-right"></i>
         <span><?php echo htmlspecialchars($product['name']); ?></span>
      </div>

      <!-- Product Detail Card -->
      <div class="product-detail-card">
         <div class="product-image">
            <img src="uploaded_img/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
         </div>
         
         <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">
               <span>₹</span><?php echo $product['price']; ?>/-
            </div>
            
            <div class="product-description">
               <?php 
               // Check if description column exists and has content
               if(isset($product['description']) && !empty($product['description'])) {
                  echo htmlspecialchars($product['description']);
               } else {
                  echo "Discover the magic of storytelling with this exceptional book. Immerse yourself in a world of imagination and embark on an unforgettable literary journey that will captivate your mind and touch your heart.";
               }
               ?>
            </div>
            
            <div class="product-features">
               <div class="features-grid">
                  <div class="feature-item">
                     <i class="fas fa-truck"></i>
                     <span>Free Delivery</span>
                  </div>
                  <div class="feature-item">
                     <i class="fas fa-undo"></i>
                     <span>Easy Returns</span>
                  </div>
                  <div class="feature-item">
                     <i class="fas fa-shield-alt"></i>
                     <span>Secure Payment</span>
                  </div>
                  <div class="feature-item">
                     <i class="fas fa-star"></i>
                     <span>Premium Quality</span>
                  </div>
               </div>
            </div>
            
            <form action="" method="post" class="product-actions">
               <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
               <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
               <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">
               
               <div class="quantity-selector">
                  <label for="quantity">Quantity:</label>
                  <input type="number" name="product_quantity" id="quantity" min="1" max="10" value="1">
               </div>
               
               <button type="submit" name="add_to_cart" class="btn-add-to-cart">
                  <i class="fas fa-shopping-cart"></i>
                  Add to Cart
               </button>
               
               <a href="shop.php" class="btn-back">
                  <i class="fas fa-arrow-left"></i>
                  Back to Shop
               </a>
            </form>
         </div>
      </div>

      <!-- Related Products -->
      <?php if(mysqli_num_rows($related_products) > 0): ?>
      <div class="related-products">
         <h2>Related Books</h2>
         <div class="related-grid">
            <?php while($related = mysqli_fetch_assoc($related_products)): ?>
            <div class="related-item">
               <img src="uploaded_img/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
               <h3><?php echo htmlspecialchars($related['name']); ?></h3>
               <div class="price">₹<?php echo $related['price']; ?>/-</div>
               <a href="book_details.php?id=<?php echo $related['id']; ?>" class="btn-view">
                  View Details
               </a>
            </div>
            <?php endwhile; ?>
         </div>
      </div>
      <?php endif; ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script>
   // Initialize GSAP animations
   gsap.registerPlugin(ScrollTrigger);
   
   // Animate product detail card on load
   gsap.from(".product-detail-card", {
      duration: 1,
      y: 50,
      opacity: 0,
      ease: "power2.out"
   });
   
   // Animate related products
   gsap.from(".related-item", {
      duration: 0.8,
      y: 30,
      opacity: 0,
      stagger: 0.2,
      ease: "power2.out",
      scrollTrigger: {
         trigger: ".related-products",
         start: "top 80%"
      }
   });
   
   // Animate breadcrumb
   gsap.from(".breadcrumb", {
      duration: 0.8,
      x: -30,
      opacity: 0,
      ease: "power2.out",
      delay: 0.3
   });
   
   // Add interactive hover effects
   document.querySelectorAll('.btn-add-to-cart, .btn-back, .btn-view').forEach(btn => {
      btn.addEventListener('mouseenter', function() {
         gsap.to(this, {
            duration: 0.3,
            scale: 1.05,
            ease: "power2.out"
         });
      });
      
      btn.addEventListener('mouseleave', function() {
         gsap.to(this, {
            duration: 0.3,
            scale: 1,
            ease: "power2.out"
         });
      });
   });
   
   // Quantity input validation
   const quantityInput = document.getElementById('quantity');
   quantityInput.addEventListener('input', function() {
      if(this.value < 1) this.value = 1;
      if(this.value > 10) this.value = 10;
   });
   
   // Image zoom effect
   const productImage = document.querySelector('.product-image img');
   productImage.addEventListener('click', function() {
      // Create modal for image zoom (optional enhancement)
      const modal = document.createElement('div');
      modal.style.cssText = `
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0,0,0,0.8);
         display: flex;
         justify-content: center;
         align-items: center;
         z-index: 10000;
         cursor: pointer;
      `;
      
      const img = document.createElement('img');
      img.src = this.src;
      img.style.cssText = `
         max-width: 90%;
         max-height: 90%;
         border-radius: 10px;
         box-shadow: 0 20px 40px rgba(0,0,0,0.3);
      `;
      
      modal.appendChild(img);
      document.body.appendChild(modal);
      
      modal.addEventListener('click', function() {
         gsap.to(modal, {
            duration: 0.3,
            opacity: 0,
            onComplete: () => document.body.removeChild(modal)
         });
      });
      
      gsap.from(modal, {
         duration: 0.3,
         opacity: 0
      });
   });
   
   // Add to cart button loading state
   const addToCartBtn = document.querySelector('.btn-add-to-cart');
   const addToCartForm = addToCartBtn.closest('form');
   
   addToCartForm.addEventListener('submit', function() {
      addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
      addToCartBtn.disabled = true;
      
      // Re-enable after 2 seconds (adjust as needed)
      setTimeout(() => {
         addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
         addToCartBtn.disabled = false;
      }, 2000);
   });
</script>

</body>
</html>
