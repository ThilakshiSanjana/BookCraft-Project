<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$message = [];

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

if(isset($_POST['add_to_cart'])){
   $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
   $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
   $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
   $product_quantity = (int)$_POST['product_quantity'];

   // Fix 1: Check for valid quantity
   if($product_quantity <= 0) {
      $message[] = 'Please select a valid quantity!';
   } else {
      $check_stmt = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
      $check_stmt->bind_param("si", $product_name, $user_id);
      $check_stmt->execute();
      $check_result = $check_stmt->get_result();

      if($check_result->num_rows > 0){
         $message[] = 'already added to cart!';
      } else {
         // Fix 2: Corrected parameter types - 'i' for integer quantity instead of 's'
         $insert_stmt = $conn->prepare("INSERT INTO cart(user_id, name, price, quantity, image) VALUES(?, ?, ?, ?, ?)");
         $insert_stmt->bind_param("issis", $user_id, $product_name, $product_price, $product_quantity, $product_image);
         
         // Fix 3: Add error handling for the insert operation
         if($insert_stmt->execute()) {
            $message[] = 'product added to cart!';
         } else {
            $message[] = 'failed to add product to cart!';
         }
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
   <title>BookCraft | Find Your Perfect Book</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      :root {
         --primary: #2e4057;
         --secondary: #ff6b6b;
         --accent: #4ecdc4;
         --light: #f7f7f7;
         --dark: #1a1a1a;
         --white: #ffffff;
         --gray: #e0e0e0;
         --text: #333333;
         --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
         --hover-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
         --transition-fast: 0.2s ease;
         --transition-medium: 0.3s ease;
         --transition-slow: 0.4s cubic-bezier(0.19, 1, 0.22, 1);
         --border-radius-sm: 6px;
         --border-radius-md: 10px;
         --border-radius-lg: 16px;
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Montserrat', sans-serif;
         transition: all var(--transition-fast);
      }
      
      body {
         background-color: var(--light);
         color: var(--text);
         line-height: 1.6;
         overflow-x: hidden;
      }
      
      .container {
         max-width: 1280px;
         margin: 0 auto;
         padding: 0 20px;
         width: 100%;
      }
      
      /* Header Section */
      .heading {
         background-color: var(--white);
         padding: 45px 0;
         text-align: center;
         position: relative;
         overflow: hidden;
         box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      }
      
      .heading::before {
         content: '';
         position: absolute;
         width: 100%;
         height: 5px;
         bottom: 0;
         left: 0;
         background: linear-gradient(90deg, var(--accent), var(--secondary));
      }
      
      .heading h3 {
         font-size: 2.75rem;
         font-weight: 700;
         color: var(--primary);
         margin-bottom: 12px;
         letter-spacing: -0.5px;
         animation: fadeInDown 0.8s ease;
      }
      
      .heading p {
         font-size: 1rem;
         color: var(--text);
         opacity: 0.8;
         animation: fadeInUp 0.8s ease;
      }
      
      .heading p a {
         color: var(--secondary);
         text-decoration: none;
         font-weight: 500;
         position: relative;
         padding-bottom: 2px;
      }
      
      .heading p a::after {
         content: '';
         position: absolute;
         width: 0;
         height: 2px;
         bottom: 0;
         left: 0;
         background-color: var(--secondary);
         transition: width 0.3s ease;
      }
      
      .heading p a:hover::after {
         width: 100%;
      }
      
      /* Search Form */
      .search-form {
         background-color: var(--white);
         padding: 35px 0;
         margin-bottom: 35px;
         box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
      }
      
      .search-form form {
         display: flex;
         align-items: center;
         max-width: 720px;
         margin: 0 auto;
         position: relative;
      }
      
      .search-form .box {
         flex: 1;
         background-color: var(--light);
         border: 2px solid transparent;
         border-radius: var(--border-radius-md);
         font-size: 1.05rem;
         padding: 18px 22px;
         outline: none;
         transition: all var(--transition-medium);
      }
      
      .search-form .box:focus {
         border-color: var(--accent);
         box-shadow: 0 0 0 4px rgba(78, 205, 196, 0.15);
      }
      
      .search-form .btn {
         background-color: var(--secondary);
         color: var(--white);
         border: none;
         border-radius: var(--border-radius-md);
         padding: 0 32px;
         height: 58px;
         font-weight: 600;
         font-size: 1rem;
         cursor: pointer;
         margin-left: 14px;
         letter-spacing: 0.5px;
         box-shadow: 0 4px 12px rgba(255, 107, 107, 0.25);
         display: flex;
         align-items: center;
         justify-content: center;
         transition: all var(--transition-medium);
      }
      
      .search-form .btn i {
         margin-right: 8px;
         font-size: 1.1rem;
      }
      
      .search-form .btn:hover {
         background-color: #ff5252;
         transform: translateY(-3px);
         box-shadow: 0 6px 18px rgba(255, 107, 107, 0.35);
      }
      
      .search-form .btn:active {
         transform: translateY(0);
      }
      
      /* Products Section */
      .products {
         padding: 20px 0 70px;
      }
      
      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 30px;
      }
      
      .box {
         background-color: var(--white);
         border-radius: var(--border-radius-lg);
         overflow: hidden;
         box-shadow: var(--box-shadow);
         transition: transform var(--transition-slow), box-shadow var(--transition-slow);
         position: relative;
         animation: fadeIn 0.8s ease forwards;
         opacity: 0;
      }
      
      .box:hover {
         transform: translateY(-10px);
         box-shadow: var(--hover-shadow);
      }
      
      .box .image-container {
         height: 220px;
         width: 100%;
         background: linear-gradient(to right, #f3f3f3, #f9f9f9);
         position: relative;
         overflow: hidden;
      }
      
      .box .image {
         height: 200px;
         width: 130px;
         object-fit: cover;
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         border-radius: 4px;
         box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
         transition: all var(--transition-slow);
      }
      
      .box:hover .image {
         transform: translate(-50%, -50%) scale(1.12);
         box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
      }
      
      .box .content {
         padding: 25px;
      }
      
      .box .name {
         font-family: 'Lora', serif;
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 12px;
         min-height: 60px;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }
      
      .box .price {
         font-size: 1.3rem;
         color: var(--secondary);
         font-weight: 600;
         margin-bottom: 18px;
         display: flex;
         align-items: center;
      }
      
      .box .price::before {
         content: "";
         display: inline-block;
         width: 35px;
         height: 3px;
         background: linear-gradient(to right, var(--secondary), #ff9191);
         margin-right: 12px;
         border-radius: 3px;
      }
      
      .product-actions {
         display: flex;
         align-items: center;
         justify-content: space-between;
      }
      
      .quantity-selector {
         display: flex;
         align-items: center;
         background-color: var(--light);
         border-radius: var(--border-radius-sm);
         overflow: hidden;
         width: 120px;
         height: 44px;
      }
      
      .quantity-btn {
         width: 38px;
         height: 44px;
         background: none;
         border: none;
         font-size: 1.1rem;
         color: var(--primary);
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         transition: all var(--transition-medium);
      }
      
      .quantity-btn:hover {
         background-color: rgba(46, 64, 87, 0.08);
         color: var(--secondary);
      }
      
      .box .qty {
         width: 44px;
         height: 44px;
         border: none;
         background: none;
         text-align: center;
         font-size: 1rem;
         font-weight: 500;
         -moz-appearance: textfield;
      }
      
      .box .qty::-webkit-outer-spin-button,
      .box .qty::-webkit-inner-spin-button {
         -webkit-appearance: none;
         margin: 0;
      }
      
      .add-btn {
         width: 48px;
         height: 48px;
         background-color: var(--accent);
         color: var(--white);
         border: none;
         border-radius: var(--border-radius-md);
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.3rem;
         box-shadow: 0 4px 12px rgba(78, 205, 196, 0.25);
         transition: all var(--transition-medium);
         position: relative;
         overflow: hidden;
         z-index: 1;
      }
      
      .add-btn::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         width: 0;
         height: 0;
         background-color: rgba(255, 255, 255, 0.2);
         border-radius: 50%;
         transform: translate(-50%, -50%);
         transition: width 0.5s, height 0.5s;
         z-index: -1;
      }
      
      .add-btn:hover {
         background-color: #3dbeb5;
         transform: translateY(-3px);
         box-shadow: 0 8px 16px rgba(78, 205, 196, 0.35);
      }
      
      .add-btn:hover::after {
         width: 150%;
         height: 150%;
      }
      
      .add-btn i {
         transition: transform 0.3s ease;
      }
      
      .add-btn:hover i {
         transform: scale(1.1);
      }
      
      /* Empty State */
      .empty {
         text-align: center;
         padding: 50px 0;
         font-size: 1.15rem;
         color: #666;
         background-color: var(--white);
         border-radius: var(--border-radius-lg);
         box-shadow: var(--box-shadow);
         margin: 25px auto;
         max-width: 720px;
         animation: fadeIn 0.8s ease;
      }
      
      .empty i {
         font-size: 3.5rem;
         color: #ddd;
         margin-bottom: 20px;
         display: block;
      }
      
      /* Toast Notification */
      .toast {
         position: fixed;
         bottom: 30px;
         right: 30px;
         background-color: var(--white);
         color: var(--text);
         padding: 18px 24px;
         border-radius: var(--border-radius-md);
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
         display: flex;
         align-items: center;
         min-width: 320px;
         transform: translateX(150%);
         opacity: 0;
         transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.35);
         z-index: 1000;
      }
      
      .toast.active {
         transform: translateX(0);
         opacity: 1;
      }
      
      .toast i {
         font-size: 1.6rem;
         margin-right: 16px;
      }
      
      .toast.success {
         border-left: 4px solid var(--accent);
      }
      
      .toast.success i {
         color: var(--accent);
      }
      
      .toast.error {
         border-left: 4px solid var(--secondary);
      }
      
      .toast.error i {
         color: var(--secondary);
      }
      
      /* Search Suggestions */
      .search-suggestions {
         position: absolute;
         top: 100%;
         left: 0;
         width: calc(100% - 130px);
         background-color: var(--white);
         border-radius: var(--border-radius-md);
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
         margin-top: 10px;
         z-index: 10;
         display: none;
         max-height: 320px;
         overflow-y: auto;
         overflow-x: hidden;
         animation: fadeInDown 0.3s ease;
      }
      
      .suggestion-item {
         padding: 16px 22px;
         cursor: pointer;
         border-bottom: 1px solid var(--gray);
         display: flex;
         align-items: center;
         transition: all var(--transition-medium);
      }
      
      .suggestion-item:last-child {
         border-bottom: none;
      }
      
      .suggestion-item:hover {
         background-color: rgba(78, 205, 196, 0.08);
         padding-left: 28px;
      }
      
      .suggestion-item i {
         color: var(--accent);
         margin-right: 14px;
         font-size: 0.95rem;
      }
      
      /* Animations */
      @keyframes fadeIn {
         from {
            opacity: 0;
         }
         to {
            opacity: 1;
         }
      }
      
      @keyframes fadeInDown {
         from {
            opacity: 0;
            transform: translateY(-20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
      
      @keyframes fadeInUp {
         from {
            opacity: 0;
            transform: translateY(20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
      
      /* Loading Spinner */
      .loading-spinner {
         display: none;
         justify-content: center;
         align-items: center;
         padding: 50px 0;
      }
      
      .spinner {
         width: 50px;
         height: 50px;
         border: 4px solid rgba(78, 205, 196, 0.2);
         border-top: 4px solid var(--accent);
         border-radius: 50%;
         animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
         0% { transform: rotate(0deg); }
         100% { transform: rotate(360deg); }
      }
      
      /* Responsive Design */
      @media (max-width: 992px) {
         .box-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
         }
         
         .heading h3 {
            font-size: 2.25rem;
         }
      }
      
      @media (max-width: 768px) {
         .heading h3 {
            font-size: 2rem;
         }
         
         .box-container {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
         }
         
         .search-form form {
            flex-direction: column;
            align-items: stretch;
         }
         
         .search-form .box {
            width: 100%;
         }
         
         .search-form .btn {
            width: 100%;
            margin: 12px 0 0 0;
         }
         
         .search-suggestions {
            width: 100%;
         }
         
         .toast {
            left: 20px;
            right: 20px;
            bottom: 20px;
            min-width: auto;
         }
      }
      
      @media (max-width: 576px) {
         .box-container {
            grid-template-columns: 1fr;
            max-width: 320px;
            margin: 0 auto;
         }
         
         .heading {
            padding: 35px 0;
         }
         
         .heading h3 {
            font-size: 1.75rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <div class="container">
      <h3>Find Your Perfect Book</h3>
      <p><a href="home.php"><i class="fas fa-home"></i> Home</a> / Search</p>
   </div>
</div>

<section class="search-form">
   <div class="container">
      <form action="" method="post" id="search-form">
         <input type="text" id="search-input" name="search" placeholder="Search by title, author, or genre..." class="box">
         <button type="submit" name="submit" class="btn">
            <i class="fas fa-search"></i> Search
         </button>
         <div class="search-suggestions" id="search-suggestions"></div>
      </form>
   </div>
</section>

<section class="products">
   <div class="container">
      <!-- Loading Spinner -->
      <div class="loading-spinner" id="loading-spinner">
         <div class="spinner"></div>
      </div>
      
      <div class="box-container" id="results-container">
      <?php
         if(isset($_POST['submit'])){
            $search_item = mysqli_real_escape_string($conn, $_POST['search']);
            
            // Fix 4: Improve search query to include author and genre
            $search_stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR author LIKE ? OR category LIKE ?");
            $search_pattern = "%{$search_item}%";
            $search_stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
            
            // Fix 5: Add error handling for search query
            if(!$search_stmt->execute()) {
               echo '<div class="empty"><i class="fas fa-exclamation-circle"></i><p>Search error occurred. Please try again.</p></div>';
            } else {
               $select_products = $search_stmt->get_result();
               
               if($select_products->num_rows > 0){
                  while($fetch_product = $select_products->fetch_assoc()){
      ?>
      <form action="" method="post" class="box">
         <div class="image-container">
            <img src="uploaded_img/<?php echo htmlspecialchars($fetch_product['image']); ?>" alt="<?php echo htmlspecialchars($fetch_product['name']); ?>" class="image">
         </div>
         <div class="content">
            <div class="name"><?php echo htmlspecialchars($fetch_product['name']); ?></div>
            <div class="price">Rs<?php echo htmlspecialchars($fetch_product['price']); ?></div>
            
            <div class="product-actions">
               <div class="quantity-selector">
                  <button type="button" class="quantity-btn decrease"><i class="fas fa-minus"></i></button>
                  <input type="number" class="qty" name="product_quantity" min="1" value="1">
                  <button type="button" class="quantity-btn increase"><i class="fas fa-plus"></i></button>
               </div>
               
               <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_product['name']); ?>">
               <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_product['price']); ?>">
               <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_product['image']); ?>">
               <button type="submit" class="add-btn" name="add_to_cart">
                  <i class="fas fa-shopping-cart"></i>
               </button>
            </div>
         </div>
      </form>
      <?php
                  }
               } else {
                  echo '<div class="empty">
                     <i class="fas fa-search"></i>
                     <p>No results found for "<strong>' . htmlspecialchars($search_item) . '</strong>".<br>Try different keywords or browse our collection.</p>
                  </div>';
               }
            }
         } else {
            echo '<div class="empty">
               <i class="fas fa-book-open"></i>
               <p>Start searching to discover amazing books in our collection!</p>
            </div>';
         }
      ?>
      </div>
   </div>
</section>

<!-- Toast Notification -->
<div class="toast" id="toast">
   <i class="fas fa-check-circle"></i>
   <span id="toast-message"></span>
</div>

<?php include 'footer.php'; ?>

<!-- Custom JS -->
<script src="js/script.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Elements
      const searchInput = document.getElementById('search-input');
      const searchForm = document.getElementById('search-form');
      const suggestionsContainer = document.getElementById('search-suggestions');
      const loadingSpinner = document.getElementById('loading-spinner');
      const resultsContainer = document.getElementById('results-container');
      
      // Sample book suggestions - in a real app, these would come from AJAX
      const bookSuggestions = [
         'The Great Gatsby', 'To Kill a Mockingbird', '1984', 
         'Pride and Prejudice', 'The Hobbit', 'Harry Potter', 
         'The Lord of the Rings', 'The Catcher in the Rye',
         'The Da Vinci Code', 'The Alchemist', 'Gone Girl',
         'The Shining', 'Sapiens', 'Dune', 'The Silent Patient'
      ];
      
      // Search suggestions feature with debounce
      let debounceTimer;
      
      searchInput.addEventListener('input', function() {
         clearTimeout(debounceTimer);
         
         debounceTimer = setTimeout(() => {
            const query = this.value.toLowerCase().trim();
            
            if (query.length < 2) {
               suggestionsContainer.style.display = 'none';
               return;
            }
            
            const matches = bookSuggestions.filter(book => 
               book.toLowerCase().includes(query)
            );
            
            if (matches.length > 0) {
               suggestionsContainer.innerHTML = '';
               
               matches.forEach(match => {
                  const div = document.createElement('div');
                  div.className = 'suggestion-item';
                  
                  // Highlight the matching part
                  const regex = new RegExp(`(${query})`, 'gi');
                  const highlightedText = match.replace(regex, '<strong>$1</strong>');
                  
                  div.innerHTML = `<i class="fas fa-book"></i> ${highlightedText}`;
                  
                  div.addEventListener('click', function() {
                     searchInput.value = match;
                     suggestionsContainer.style.display = 'none';
                     searchForm.submit();
                  });
                  
                  suggestionsContainer.appendChild(div);
               });
               
               suggestionsContainer.style.display = 'block';
            } else {
               suggestionsContainer.style.display = 'none';
            }
         }, 300); // 300ms debounce
      });
      
      // Form submission with loading state
      searchForm.addEventListener('submit', function(e) {
         if (searchInput.value.trim() === '') {
            e.preventDefault();
            showToast('Please enter a search term', 'error');
            return;
         }
         
         loadingSpinner.style.display = 'flex';
         resultsContainer.style.opacity = '0.5';
      });
      
      // Close suggestions when clicking elsewhere
      document.addEventListener('click', function(e) {
         if (e.target !== searchInput) {
            suggestionsContainer.style.display = 'none';
         }
      });
      
      // Fix 6: Improved quantity selector functionality using event delegation
      document.addEventListener('click', function(e) {
         // Check if the clicked element is a quantity button
         if (e.target.closest('.quantity-btn')) {
            const button = e.target.closest('.quantity-btn');
            const input = button.parentElement.querySelector('.qty');
            const currentValue = parseInt(input.value);
            
            if (button.classList.contains('increase')) {
               input.value = currentValue + 1;
            } else if (button.classList.contains('decrease') && currentValue > 1) {
               input.value = currentValue - 1;
            }
         }
      });
      
      // Animate product cards on scroll
      const animateOnScroll = () => {
         const productCards = document.querySelectorAll('.box');
         
         productCards.forEach((card, index) => {
            const cardTop = card.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (cardTop < windowHeight * 0.9) {
               card.style.opacity = '1';
               card.style.animationDelay = `${index * 0.1}s`;
            }
         });
      };
      
      // Run once on load
      animateOnScroll();
      
      // And on scroll
      window.addEventListener('scroll', animateOnScroll);
   });
   
   // Toast notification
   function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      const toastMessage = document.getElementById('toast-message');
      const icon = toast.querySelector('i');
      
      // Reset classes
      toast.className = 'toast';
      toast.classList.add(type);
      
      if (type === 'success') {
         icon.className = 'fas fa-check-circle';
      } else {
         icon.className = 'fas fa-exclamation-circle';
      }
      
      toastMessage.textContent = message;
      
      // Add active class to show toast
      toast.classList.add('active');
      
      // Auto hide after delay
      setTimeout(() => {
         toast.classList.remove('active');
      }, 3500);
      
      // Allow clicking to dismiss
      toast.addEventListener('click', () => {
         toast.classList.remove('active');
      });
   }
   
   // Handle cart messages from PHP
   <?php if(isset($message) && !empty($message)): ?>
      <?php foreach($message as $msg): ?>
         <?php if(strpos($msg, 'already') !== false || strpos($msg, 'failed') !== false): ?>
            showToast("<?php echo addslashes($msg); ?>", 'error');
         <?php else: ?>
            showToast("<?php echo addslashes($msg); ?>", 'success');
         <?php endif; ?>
      <?php endforeach; ?>
   <?php endif; ?>
</script>

</body>
</html>