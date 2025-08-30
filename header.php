<?php


if(isset($message)){
   echo '<div class="notifications-container">';
   foreach($message as $message){
      echo '
      <div class="notification" id="notification-'.uniqid().'">
         <div class="notification-content">
            <i class="fas fa-info-circle notification-icon"></i>
            <span class="notification-message">'.$message.'</span>
         </div>
         <button class="notification-close" onclick="this.parentElement.classList.add(\'fade-out\'); setTimeout(() => this.parentElement.remove(), 300);">
            <i class="fas fa-times"></i>
         </button>
      </div>
      ';
   }
   echo '</div>';
}
?>

<header class="header">
   <!-- Top bar with social links and auth -->
   <div class="header-top">
      <div class="container">
         <div class="header-top-wrapper">
            <div class="social-links">
               <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
               <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
               <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
               <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="auth-links">
               <a href="login.php" class="auth-link"><i class="fas fa-sign-in-alt"></i> Login</a>
               <span class="divider">|</span>
               <a href="register.php" class="auth-link"><i class="fas fa-user-plus"></i> Register</a>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Main navigation bar -->
   <div class="header-main">
      <div class="container">
         <div class="header-main-wrapper">
            <a href="home.php" class="brand-logo">
               <div class="logo">
                  <div class="logo-icon">
                     <i class="fas fa-book-open"></i>
                  </div>
                  <div class="logo-text">
                     <span class="logo-title">BookCraft</span>
                     <span class="logo-tagline">Your Literary Journey</span>
                  </div>
               </div>
            </a>

            <nav class="navbar" id="navbar">
               <ul class="nav-list">
                  <li class="nav-item"><a href="home.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">Home</a></li>
                  <li class="nav-item"><a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                  <li class="nav-item"><a href="shop.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>">Shop</a></li>
                  <li class="nav-item"><a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                  <li class="nav-item"><a href="orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">Orders</a></li>
               </ul>
            </nav>

            <div class="header-actions">
               <button class="action-button search-toggle" aria-label="Search">
                  <i class="fas fa-search"></i>
               </button>
               
               <!-- Theme toggle button -->
               <button class="action-button theme-toggle" id="theme-toggle" aria-label="Toggle dark/light mode">
                  <i class="fas fa-moon"></i>
               </button>
               
               <div class="user-dropdown">
                  <button class="action-button user-toggle" aria-label="User account">
                     <i class="fas fa-user"></i>
                  </button>
                  <div class="user-dropdown-content">
                     <div class="user-info">
                        <div class="user-avatar">
                           <span><?php echo isset($_SESSION['user_name']) ? substr($_SESSION['user_name'], 0, 1) : 'G'; ?></span>
                        </div>
                        <div class="user-details">
                           <p class="user-name"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest'; ?></p>
                           <p class="user-email"><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?></p>
                        </div>
                     </div>
                     <div class="dropdown-divider"></div>
                    
                     <a href="orders.php" class="dropdown-item">
                        <i class="fas fa-box"></i> My Orders
                     </a>
                  
                     <div class="dropdown-divider"></div>
                     <a href="logout.php" class="dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                     </a>
                  </div>
               </div>

               <?php
                  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                  $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                  $cart_rows_number = mysqli_num_rows($select_cart_number); 
               ?>
               <a href="cart.php" class="action-button cart-button" aria-label="Shopping cart">
                  <i class="fas fa-shopping-cart"></i>
                  <?php if($cart_rows_number > 0): ?>
                  <span class="cart-count"><?php echo $cart_rows_number; ?></span>
                  <?php endif; ?>
               </a>
               
               <button class="action-button menu-toggle" id="menu-toggle" aria-label="Menu">
                  <i class="fas fa-bars"></i>
               </button>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Search overlay -->
   <div class="search-overlay">
      <div class="container">
         <form action="search_page.php" method="post" class="search-form">
            <input type="text" name="search" placeholder="Search for books, authors, genres..." required>
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
         </form>
         <button class="search-close"><i class="fas fa-times"></i></button>
      </div>
   </div>
</header>

<!-- Additional CSS for the header with logo -->
<style>
:root {
   --header-height: 8rem;
   --header-top-height: 4rem;
   --primary-color: #5e35b1;  /* Purple shade */
   --primary-dark: #4527a0;   /* Darker purple */
   --secondary-color: #283593; /* Deep blue */
   --accent-color: #ff9800;   /* Orange */
   --text-color: #333;
   --text-light: #777;
   --bg-light: #f6f6f6;
   --white: #fff;
   --shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
   --transition: all 0.3s ease;
   --radius: 0.5rem;
   --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

/* Theme toggle specific styles */
.theme-toggle i.fa-sun {
   color: var(--accent-color);
}

.theme-toggle i.fa-moon {
   color: var(--secondary-color);
}

/* Dark mode classes */
:root.dark-mode {
   --text-color: #f5f5f5;
   --text-light: #aaa;
   --bg-light: #333;
   --white: #222;
   --shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.3);
}

:root.dark-mode .header {
   background-color: #222;
}

:root.dark-mode .header-main {
   background-color: #222;
}

:root.dark-mode .search-overlay {
   background: rgba(25, 32, 90, 0.97);
}

:root.dark-mode .user-dropdown-content,
:root.dark-mode .notification,
:root.dark-mode .search-form input,
:root.dark-mode .navbar.active {
   background-color: #333;
   color: #f5f5f5;
}

:root.dark-mode .user-dropdown-content::before {
   background-color: #333;
}

:root.dark-mode .dropdown-divider {
   background-color: #444;
}

:root.dark-mode .dropdown-item:hover {
   background-color: rgba(94, 53, 177, 0.2);
}

:root.dark-mode .nav-link,
:root.dark-mode .action-button {
   color: #f5f5f5;
}

:root.dark-mode .cart-count {
   background-color: var(--accent-color);
   color: #222;
}

:root.dark-mode .logo-tagline {
   color: #aaa;
}

/* Notifications styling */
.notifications-container {
   position: fixed;
   top: 2rem;
   right: 2rem;
   z-index: 9999;
   display: flex;
   flex-direction: column;
   gap: 1rem;
   max-width: 35rem;
}

.notification {
   background: var(--white);
   border-radius: var(--radius);
   box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
   padding: 1.5rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   animation: slideIn 0.3s ease-out forwards;
   border-left: 4px solid var(--primary-color);
}

.notification.fade-out {
   animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideIn {
   from { transform: translateX(100%); opacity: 0; }
   to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
   from { transform: translateX(0); opacity: 1; }
   to { transform: translateX(100%); opacity: 0; }
}

.notification-content {
   display: flex;
   align-items: center;
   gap: 1rem;
}

.notification-icon {
   color: var(--primary-color);
   font-size: 1.8rem;
}

.notification-message {
   font-size: 1.4rem;
   color: var(--text-color);
}

.notification-close {
   background: none;
   border: none;
   color: var(--text-light);
   cursor: pointer;
   font-size: 1.4rem;
   padding: 0.5rem;
   transition: var(--transition);
}

.notification-close:hover {
   color: var(--text-color);
}

/* Header styling */
.header {
   position: sticky;
   top: 0;
   width: 100%;
   z-index: 1000;
   background-color: var(--white);
   box-shadow: var(--shadow);
   height:60px;
   transition: var(--transition);
}

.container {
   width: 100%;
   max-width: 120rem;
   margin: 0 auto;
   padding: 0 2rem;
   height:50px;
}

/* Top bar */
.header-top {
   background: var(--gradient);
   height: var(--header-top-height);
   color: var(--white);
   height:60px;
   padding:5px;
}

.header-top-wrapper {
   display: flex;
   justify-content: space-between;
   align-items: center;
   height: 100%;
}

.social-links {
   display: flex;
   gap: 1.5rem;
}

.social-link {
   color: var(--white);
   font-size: 2rem;
   opacity: 0.8;
   transition: var(--transition);
}

.social-link:hover {
   opacity: 1;
   transform: translateY(-2px);
}

.auth-links {
   display: flex;
   align-items: center;
   gap: 1rem;
}

.auth-link {
   color: var(--white);
   font-size: 1.4rem;
   transition: var(--transition);
}

.auth-link:hover {
   color: var(--accent-color);
}

.divider {
   opacity: 0.5;
}

/* Main header */
.header-main {
   height: var(--header-height);
   background-color: var(--white);
   transition: var(--transition);
}

.header-main-wrapper {
   display: flex;
   justify-content: space-between;
   align-items: center;
   height: 80px;
}

/* Logo styling */
.brand-logo {
   text-decoration: none;
   display: flex;
   align-items: center;
}

.logo {
   display: flex;
   align-items: center;
   gap: 1.2rem;
}

.logo-icon {
   width: 4.5rem;
   height: 4.5rem;
   border-radius: 50%;
   background: var(--gradient);
   display: flex;
   align-items: center;
   justify-content: center;
   color: var(--white);
   font-size: 2.2rem;
   box-shadow: 0 0.5rem 1.5rem rgba(94, 53, 177, 0.3);
   position: relative;
   overflow: hidden;
}

.logo-icon::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: rgba(255, 255, 255, 0.2);
   transform: skewX(-30deg);
   transition: 0.5s;
}

.brand-logo:hover .logo-icon::before {
   left: 100%;
}

.logo-text {
   display: flex;
   flex-direction: column;
}

.logo-title {
   font-size: 2.6rem;
   font-weight: 700;
   background: var(--gradient);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   letter-spacing: -0.5px;
}

.logo-tagline {
   font-size: 1.3rem;
   font-weight: 400;
   color: var(--text-dark);
   letter-spacing: 0.5px;
   transition: var(--transition);
}

/* Navigation */
.navbar {
   margin-left: 3rem;
}

.nav-list {
   display: flex;
   gap: 2.5rem;
   list-style: none;
}

.nav-link {
   position: relative;
   display: inline-block;
   color: var(--text-color);
   font-size: 1.8rem;
   font-weight: 500;
   padding: 1rem 0;
   text-decoration: none;
   transition: var(--transition);
}

.nav-link:hover, .nav-link.active {
   color: var(--primary-color);
}

.nav-link::after {
   content: '';
   position: absolute;
   bottom: 0;
   left: 0;
   width: 0;
   height: 2px;
   background: var(--gradient);
   transition: var(--transition);
}

.nav-link:hover::after, .nav-link.active::after {
   width: 100%;
}

/* Header actions */
.header-actions {
   display: flex;
   align-items: center;
   gap: 1.8rem;
}

.action-button {
   background: none;
   border: none;
   width: 4rem;
   height: 4rem;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 1.8rem;
   color: var(--text-color);
   cursor: pointer;
   transition: var(--transition);
   position: relative;
   overflow: hidden;
}

.action-button::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(94, 53, 177, 0.1);
   border-radius: 50%;
   transform: scale(0);
   transition: var(--transition);
}

.action-button:hover::before {
   transform: scale(1);
}

.action-button:hover {
   color: var(--primary-color);
}

/* Cart button */
.cart-button {
   position: relative;
}

.cart-count {
   position: absolute;
   top: -0.5rem;
   right: -0.5rem;
   width: 2rem;
   height: 2rem;
   border-radius: 50%;
   background: var(--accent-color);
   color: var(--white);
   font-size: 1.2rem;
   font-weight: 600;
   display: flex;
   align-items: center;
   justify-content: center;
   box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.2);
   transition: var(--transition);
}

/* User dropdown */
.user-dropdown {
   position: relative;
}

.user-dropdown-content {
   position: absolute;
   top: calc(100% + 1rem);
   right: 0;
   width: 28rem;
   background-color: var(--white);
   border-radius: var(--radius);
   box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
   padding: 2rem;
   opacity: 0;
   visibility: hidden;
   transform: translateY(1rem);
   transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
   z-index: 100;
}

.user-dropdown-content::before {
   content: '';
   position: absolute;
   top: -0.8rem;
   right: 1.5rem;
   width: 1.5rem;
   height: 1.5rem;
   background: var(--white);
   transform: rotate(45deg);
   box-shadow: -0.3rem -0.3rem 0.5rem rgba(0, 0, 0, 0.05);
   transition: var(--transition);
}

.user-dropdown:hover .user-dropdown-content,
.user-toggle:focus + .user-dropdown-content {
   opacity: 1;
   visibility: visible;
   transform: translateY(0);
}

.user-info {
   display: flex;
   align-items: center;
   gap: 1.5rem;
   margin-bottom: 1.5rem;
}

.user-avatar {
   width: 5rem;
   height: 5rem;
   border-radius: 50%;
   background: var(--gradient);
   color: var(--white);
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 2rem;
   font-weight: 600;
   box-shadow: 0 0.3rem 0.8rem rgba(94, 53, 177, 0.3);
}

.user-details {
   flex: 1;
}

.user-name {
   font-size: 1.6rem;
   font-weight: 600;
   color: var(--text-color);
   margin-bottom: 0.5rem;
   transition: var(--transition);
}

.user-email {
   font-size: 1.3rem;
   color: var(--secondary-color);
   transition: var(--transition);
}

.dropdown-divider {
   height: 1px;
   background-color: #eee;
   margin: 1.5rem 0;
   transition: var(--transition);
}

.dropdown-item {
   display: flex;
   align-items: center;
   gap: 1rem;
   padding: 1rem;
   border-radius: var(--radius);
   text-decoration: none;
   color: var(--text-color);
   font-size: 1.4rem;
   transition: var(--transition);
}

.dropdown-item:hover {
   background-color: rgba(94, 53, 177, 0.08);
   color: var(--primary-color);
}

.dropdown-item i {
   color: var(--primary-color);
   opacity: 0.8;
   font-size: 1.6rem;
}

.dropdown-item.logout {
   color: #e74c3c;
}

.dropdown-item.logout i {
   color: #e74c3c;
}

.dropdown-item.logout:hover {
   background-color: rgba(231, 76, 60, 0.08);
}

/* Search overlay */
.search-overlay {
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(40, 53, 147, 0.97);
   display: flex;
   align-items: center;
   justify-content: center;
   z-index: 2000;
   opacity: 0;
   visibility: hidden;
   transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.search-overlay.active {
   opacity: 1;
   visibility: visible;
}

.search-form {
   width: 100%;
   max-width: 65rem;
   position: relative;
   transform: translateY(50px);
   opacity: 0;
   transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
   transition-delay: 0.2s;
}

.search-overlay.active .search-form {
   transform: translateY(0);
   opacity: 1;
}

.search-form input {
   width: 100%;
   padding: 2rem 2.5rem;
   padding-right: 6rem;
   background-color: var(--white);
   border: none;
   border-radius: var(--radius);
   font-size: 1.8rem;
   color: var(--text-color);
   box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
   transition: var(--transition);
}

.search-form input:focus {
   outline: none;
   box-shadow: 0 0 0 3px var(--primary-color), 0 1rem 2rem rgba(0, 0, 0, 0.2);
}

.search-btn {
   position: absolute;
   top: 50%;
   right: 1rem;
   transform: translateY(-50%);
   background: var(--gradient);
   color: var(--white);
   border: none;
   width: 4.5rem;
   height: 4.5rem;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 1.8rem;
   cursor: pointer;
   transition: var(--transition);
   box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
}

.search-btn:hover {
   transform: translateY(-50%) scale(1.05);
   box-shadow: 0 0.8rem 1.5rem rgba(0, 0, 0, 0.3);
}

.search-close {
   position: absolute;
   top: 4rem;
   right: 4rem;
   background: none;
   border: none;
   color: var(--white);
   font-size: 3rem;
   cursor: pointer;
   transition: var(--transition);
}

.search-close:hover {
   color: var(--accent-color);
   transform: rotate(90deg);
}

/* Menu toggle (mobile) */
.menu-toggle {
   display: none;
}

/* Responsive styles */
@media (max-width: 991px) {
   .navbar {
      display: none;
      position: fixed;
      top: calc(var(--header-top-height) + var(--header-height));
      left: 0;
      width: 100%;
      background-color: var(--white);
      box-shadow: var(--shadow);
      z-index: 999;
      padding: 2rem;
      transition: var(--transition);
   }
   
   .navbar.active {
      display: block;
   }
   
   .nav-list {
      flex-direction: column;
      gap: 0;
   }
   
   .nav-link {
      display: block;
      padding: 1.5rem 0;
   }
   
   .menu-toggle {
      display: flex;
   }
}

@media (max-width: 768px) {
   .header-top {
      display: none;
   }
   
   .search-toggle, .user-toggle {
      font-size: 2rem;
   }
   
   .logo-icon {
      width: 4rem;
      height: 4rem;
      font-size: 2rem;
   }
   
   .logo-title {
      font-size: 2.2rem;
   }
}

@media (max-width: 576px) {
   .logo-tagline {
      display: none;
   }
   
   .header-actions {
      gap: 1rem;
   }
   
   .action-button {
      width: 3.5rem;
      height: 3.5rem;
   }
   
   .user-dropdown-content {
      width: 25rem;
      right: -5rem;
   }
   
   .user-dropdown-content::before {
      right: 7rem;
   }
   
   .search-close {
      top: 2rem;
      right: 2rem;
   }
}

/* Animation for logo on page load */
@keyframes logoEntrance {
   0% {
      opacity: 0;
      transform: translateY(-20px);
   }
   100% {
      opacity: 1;
      transform: translateY(0);
   }
}

.logo {
   animation: logoEntrance 0.8s ease-out forwards;
}

.logo-icon {
   opacity: 0;
   animation: fadeInRotate 0.5s ease-out 0.3s forwards;
}

@keyframes fadeInRotate {
   0% {
      opacity: 0;
      transform: scale(0.5) rotate(-30deg);
   }
   100% {
      opacity: 1;
      transform: scale(1) rotate(0);
   }
}

.logo-title, .logo-tagline {
   opacity: 0;
   animation: fadeIn 0.5s ease-out 0.5s forwards;
}

@keyframes fadeIn {
   0% { opacity: 0; }
   100% { opacity: 1; }
}

/* Dark mode media query (fallback when no JS or preference not set) */
@media (prefers-color-scheme: dark) {
   body:not(.light-mode):not(.dark-mode) {
      --text-color: #f5f5f5;
      --text-light: #aaa;
      --bg-light: #333;
      --white: #222;
      --shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.3);
   }
   
   body:not(.light-mode):not(.dark-mode) .header,
   body:not(.light-mode):not(.dark-mode) .header-main {
      background-color: #222;
   }
   
   body:not(.light-mode):not(.dark-mode) .user-dropdown-content,
   body:not(.light-mode):not(.dark-mode) .notification,
   body:not(.light-mode):not(.dark-mode) .search-form input,
   body:not(.light-mode):not(.dark-mode) .navbar.active {
      background-color: #333;
      color: #f5f5f5;
   }
   
   body:not(.light-mode):not(.dark-mode) .user-dropdown-content::before {
      background-color: #333;
   }
   
   body:not(.light-mode):not(.dark-mode) .dropdown-divider {
      background-color: #444;
   }
   
   body:not(.light-mode):not(.dark-mode) .dropdown-item:hover {
      background-color: rgba(94, 53, 177, 0.2);
   }
   
   body:not(.light-mode):not(.dark-mode) .nav-link,
   body:not(.light-mode):not(.dark-mode) .action-button {
      color: #f5f5f5;
   }
}
</style>

<!-- JS for header interactivity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
   // Mobile menu toggle
   const menuToggle = document.getElementById('menu-toggle');
   const navbar = document.getElementById('navbar');
   
   if (menuToggle && navbar) {
      menuToggle.addEventListener('click', function() {
         navbar.classList.toggle('active');
         
         // Toggle icon
         const icon = this.querySelector('i');
         if (icon.classList.contains('fa-bars')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
         } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
         }
      });
   }
   
   // Search overlay with enhanced animations
   const searchToggle = document.querySelector('.search-toggle');
   const searchOverlay = document.querySelector('.search-overlay');
   const searchClose = document.querySelector('.search-close');
   
   if (searchToggle && searchOverlay && searchClose) {
      searchToggle.addEventListener('click', function() {
         searchOverlay.classList.add('active');
         document.body.style.overflow = 'hidden';
         setTimeout(() => {
            searchOverlay.querySelector('input').focus();
         }, 500);
      });
      
      searchClose.addEventListener('click', function() {
         searchOverlay.classList.remove('active');
         document.body.style.overflow = '';
      });
      
      // Close on escape key
      document.addEventListener('keydown', function(e) {
         if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
         }
      });
   }
   
   // Auto-hide notifications after 5 seconds
   const notifications = document.querySelectorAll('.notification');
   notifications.forEach(notification => {
      setTimeout(() => {
         notification.classList.add('fade-out');
         setTimeout(() => {
            notification.remove();
         }, 300);
      }, 5000);
   });
   
   // Logo hover effect
   const logo = document.querySelector('.logo-icon');
   if (logo) {
      logo.addEventListener('mouseover', function() {
         this.style.transform = 'scale(1.1)';
      });
      
      logo.addEventListener('mouseout', function() {
         this.style.transform = 'scale(1)';
      });
   }
   
   // Theme toggle functionality - FIXED VERSION
   const themeToggle = document.getElementById('theme-toggle');
   const root = document.documentElement;
   
   // Helper function to update theme icon
   function updateThemeIcon(isDark) {
      if (!themeToggle) return;
      
      const icon = themeToggle.querySelector('i');
      if (!icon) return;
      
      if (isDark) {
         icon.classList.remove('fa-moon');
         icon.classList.add('fa-sun');
      } else {
         icon.classList.remove('fa-sun');
         icon.classList.add('fa-moon');
      }
   }
   
   // Apply theme on page load
   function applyTheme() {
      // Check for saved theme preference or use system preference
      const savedTheme = localStorage.getItem('bookcraft-theme');
      
      if (savedTheme === 'dark') {
         root.classList.add('dark-mode');
         updateThemeIcon(true);
      } else if (savedTheme === 'light') {
         root.classList.remove('dark-mode');
         updateThemeIcon(false);
      } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
         root.classList.add('dark-mode');
         updateThemeIcon(true);
      } else {
         root.classList.remove('dark-mode');
         updateThemeIcon(false);
      }
   }
   
   // Apply theme immediately
   applyTheme();
   
   // Toggle theme on button click
   if (themeToggle) {
      themeToggle.addEventListener('click', function() {
         if (root.classList.contains('dark-mode')) {
            root.classList.remove('dark-mode');
            localStorage.setItem('bookcraft-theme', 'light');
            updateThemeIcon(false);
            console.log('Switched to light mode');
         } else {
            root.classList.add('dark-mode');
            localStorage.setItem('bookcraft-theme', 'dark');
            updateThemeIcon(true);
            console.log('Switched to dark mode');
         }
      });
   }
   
   // Listen for system preference changes
   if (window.matchMedia) {
      const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      
      try {
         // Modern browsers
         darkModeMediaQuery.addEventListener('change', event => {
            if (!localStorage.getItem('bookcraft-theme')) { // Only if user hasn't manually set a preference
               if (event.matches) {
                  root.classList.add('dark-mode');
                  updateThemeIcon(true);
               } else {
                  root.classList.remove('dark-mode');
                  updateThemeIcon(false);
               }
            }
         });
      } catch (e) {
         // Fallback for older browsers
         darkModeMediaQuery.addListener(event => {
            if (!localStorage.getItem('bookcraft-theme')) {
               if (event.matches) {
                  root.classList.add('dark-mode');
                  updateThemeIcon(true);
               } else {
                  root.classList.remove('dark-mode');
                  updateThemeIcon(false);
               }
            }
         });
      }
   }
});
</script>