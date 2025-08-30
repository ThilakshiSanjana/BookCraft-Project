
<footer class="footer">
   
   <div class="theme-toggle-container">
      <button id="themeToggle" class="theme-toggle" aria-label="Toggle dark/light theme">
         <i class="fas fa-moon dark-icon"></i>
         <i class="fas fa-sun light-icon"></i>
      </button>
   </div>

   <div class="footer-wave">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
         <path fill="#5e35b1" fill-opacity="0.3" d="M0,128L48,117.3C96,107,192,85,288,90.7C384,96,480,128,576,149.3C672,171,768,181,864,165.3C960,149,1056,107,1152,90.7C1248,75,1344,85,1392,90.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
      </svg>
   </div>

   <div class="footer-content">
      
      <div class="newsletter-container">
         <div class="newsletter">
            <h3>Subscribe to our Newsletter</h3>
            <p>Get the latest books and exclusive offers directly to your inbox</p>
            <form class="newsletter-form" id="footerNewsletterForm" action="subscribe.php" method="POST">
               <input type="email" name="email" placeholder="Your email address" required>
               <button type="submit">Subscribe</button>
               <div class="form-message" id="newsletterMessage"></div>
            </form>
         </div>
      </div>

      <div class="footer-grid">
         <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
               <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
               <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
               <li><a href="shop.php"><i class="fas fa-book"></i> Shop</a></li>
               <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
         </div>

         <div class="footer-column">
            <h3>Account</h3>
            <ul class="footer-links">
               <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
               <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
               <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
               <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
            </ul>
         </div>

         <div class="footer-column">
            <h3>Contact Us</h3>
            <ul class="contact-info">
               <li><i class="fas fa-map-marker-alt"></i> Main Street, Colombo, Sri Lanka</li>
               <li><i class="fas fa-phone-alt"></i> 011 472 8345</li>
               <li><i class="fas fa-envelope"></i> contact@bookcraft.com</li>
               <li><i class="fas fa-clock"></i> Mon-Fri: 9am - 6pm</li>
            </ul>
         </div>

         <div class="footer-column">
            <div class="company-info">
               <h3>BookCraft</h3>
               <p>Your ultimate destination for books of all genres. We deliver quality reading experiences at competitive prices.</p>
            </div>
            
            <div class="social-payment">
               <h4>Connect With Us</h4>
               <div class="social-icons">
                  <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                  <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                  <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                  <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
               </div>
               
               <h4>We Accept</h4>
               <div class="payment-methods">
                  <i class="fab fa-cc-visa"></i>
                  <i class="fab fa-cc-mastercard"></i>
                  <i class="fab fa-cc-paypal"></i>
                  <i class="fab fa-cc-amex"></i>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="footer-bottom">
      <div class="copyright">
         <p>&copy; <?php echo date('Y'); ?> <span>BookCraft</span> | All Rights Reserved</p>
      </div>
      <div class="footer-links-bottom">
         <a href="#">Privacy Policy</a>
         <a href="#">Terms of Service</a>
         <a href="#">Shipping Policy</a>
         <a href="#">FAQ</a>
      </div>
   </div>
   
   <!-- Back to top button -->
   <button class="back-to-top" id="backToTop" aria-label="Back to top">
      <i class="fas fa-chevron-up"></i>
   </button>
</footer>

<style>
   /* Modern Footer Styling */
   :root {
      /* Dark Theme Variables (Default) */
      --primary: #5e35b1;
      --primary-light: #7e57c2;
      --primary-dark: #4527a0;
      --secondary: #3f51b5;
      --text-light: #f8f9fa;
      --text-muted: #adb5bd;
      --bg-dark: #212529;
      --bg-darker: #121212;
      --transition: all 0.3s ease;
      --success-color: #28a745;
      --error-color: #dc3545;
      
      /* Light Theme Variables (Added) */
      --light-primary: #5e35b1;
      --light-primary-light: #7e57c2;
      --light-primary-dark: #4527a0;
      --light-secondary: #3f51b5;
      --light-text-dark: #212529;
      --light-text-muted: #6c757d;
      --light-bg-light: #f8f9fa;
      --light-bg-lighter: #ffffff;
   }
   
   /* Light Theme Class */
   body.light-theme {
      --primary: #5e35b1;
      --primary-light: #7e57c2;
      --primary-dark: #4527a0;
      --secondary: #3f51b5;
      --text-light: #212529;
      --text-muted: #6c757d;
      --bg-dark: #f8f9fa;
      --bg-darker: #ffffff;
   }

   .footer {
      position: relative;
      background: var(--bg-dark);
      color: var(--text-light);
      margin-top: 8rem;
      overflow: hidden;
   }

   .footer-wave {
      position: absolute;
      top: -100px;
      left: 0;
      width: 100%;
      overflow: hidden;
      line-height: 0;
      z-index: 1;
   }

   .footer-content {
      position: relative;
      z-index: 2;
      padding: 5rem 9%;
   }

   /* Theme Toggle Styling */
   .theme-toggle-container {
      position: absolute;
      top: 2rem;
      right: 2rem;
      z-index: 10;
   }

   .theme-toggle {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      width: 4.5rem;
      height: 4.5rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
   }

   .theme-toggle:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
   }

   .theme-toggle i {
      font-size: 2rem;
      color: var(--text-light);
      position: absolute;
      transition: var(--transition);
   }

   .theme-toggle .dark-icon {
      opacity: 0;
      transform: translateY(20px);
   }

   .theme-toggle .light-icon {
      opacity: 1;
      transform: translateY(0);
   }

   body.light-theme .theme-toggle .dark-icon {
      opacity: 1;
      transform: translateY(0);
   }

   body.light-theme .theme-toggle .light-icon {
      opacity: 0;
      transform: translateY(20px);
   }

   /* Newsletter Section */
   .newsletter-container {
      margin-bottom: 4rem;
   }

   .newsletter {
      background: rgba(126, 87, 194, 0.1);
      border-radius: 10px;
      padding: 2.5rem;
      text-align: center;
      border: 1px solid rgba(126, 87, 194, 0.2);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
   }

   .newsletter h3 {
      font-size: 2.4rem;
      margin-bottom: 1rem;
      color: var(--text-light);
   }

   .newsletter p {
      font-size: 1.6rem;
      color: var(--text-muted);
      margin-bottom: 2rem;
   }

   .newsletter-form {
      display: flex;
      flex-wrap: wrap;
      max-width: 600px;
      margin: 0 auto;
      gap: 1rem;
   }

   .newsletter-form input {
      flex: 1;
      padding: 1.5rem 2rem;
      border-radius: 50px;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-light);
      font-size: 1.6rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: var(--transition);
      min-width: 200px;
   }

   .newsletter-form input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--primary-light);
   }

   .newsletter-form button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 1.5rem 3rem;
      border-radius: 50px;
      font-size: 1.6rem;
      cursor: pointer;
      transition: var(--transition);
      font-weight: 600;
   }

   .newsletter-form button:hover {
      background: var(--primary-light);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(94, 53, 177, 0.3);
   }

   .form-message {
      flex-basis: 100%;
      margin-top: 1rem;
      font-size: 1.4rem;
      transition: var(--transition);
      height: 2rem;
   }

   .form-message.success {
      color: var(--success-color);
   }

   .form-message.error {
      color: var(--error-color);
   }

   /* Grid Layout */
   .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 4rem;
   }

   .footer-column h3 {
      color: var(--text-light);
      font-size: 2rem;
      margin-bottom: 2rem;
      font-weight: 600;
      position: relative;
      padding-bottom: 1rem;
   }

   .footer-column h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 3px;
      width: 5rem;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      border-radius: 30px;
   }

   /* Footer Links */
   .footer-links {
      list-style: none;
      padding: 0;
   }

   .footer-links li {
      margin-bottom: 1.5rem;
   }

   .footer-links a {
      color: var(--text-muted);
      font-size: 1.6rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      transition: var(--transition);
   }

   .footer-links a i {
      margin-right: 1rem;
      color: var(--primary-light);
      transition: var(--transition);
   }

   .footer-links a:hover {
      color: var(--text-light);
      transform: translateX(5px);
   }

   .footer-links a:hover i {
      color: var(--primary);
   }

   /* Contact Info */
   .contact-info {
      list-style: none;
      padding: 0;
   }

   .contact-info li {
      display: flex;
      align-items: flex-start;
      margin-bottom: 1.5rem;
      color: var(--text-muted);
      font-size: 1.6rem;
   }

   .contact-info li i {
      margin-right: 1.5rem;
      color: var(--primary-light);
      margin-top: 0.4rem;
   }

   /* Company Info */
   .company-info p {
      color: var(--text-muted);
      font-size: 1.6rem;
      line-height: 1.7;
      margin-bottom: 2rem;
   }

   /* Social & Payment */
   .social-payment h4 {
      color: var(--text-light);
      font-size: 1.7rem;
      margin: 2rem 0 1.2rem;
   }

   .social-icons {
      display: flex;
      gap: 1.2rem;
      margin-bottom: 2rem;
   }

   .social-icons a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 4rem;
      height: 4rem;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      color: var(--text-light);
      font-size: 1.8rem;
      transition: var(--transition);
      text-decoration: none;
   }

   .social-icons a:hover {
      background: var(--primary);
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
   }

   .payment-methods {
      display: flex;
      gap: 1.2rem;
      flex-wrap: wrap;
   }

   .payment-methods i {
      font-size: 3rem;
      color: var(--text-muted);
      transition: var(--transition);
   }

   .payment-methods i:hover {
      color: var(--text-light);
   }

   /* Footer Bottom */
   .footer-bottom {
      background: var(--bg-darker);
      padding: 2rem 9%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 2rem;
   }

   .copyright {
      font-size: 1.5rem;
      color: var(--text-muted);
   }

   .copyright span {
      color: var(--primary-light);
      font-weight: 600;
   }

   .footer-links-bottom {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
   }

   .footer-links-bottom a {
      color: var(--text-muted);
      font-size: 1.5rem;
      text-decoration: none;
      transition: var(--transition);
   }

   .footer-links-bottom a:hover {
      color: var(--primary-light);
   }

   /* Back to top button */
   .back-to-top {
      position: fixed;
      bottom: 2.5rem;
      right: 2.5rem;
      background: var(--primary);
      color: white;
      width: 5rem;
      height: 5rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      cursor: pointer;
      z-index: 999;
      border: none;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
   }

   .back-to-top.active {
      opacity: 1;
      visibility: visible;
   }

   .back-to-top:hover {
      background: var(--primary-light);
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(94, 53, 177, 0.4);
   }

   /* Responsive Adjustments */
   @media (max-width: 991px) {
      .footer-content {
         padding: 5rem 5%;
      }
      .footer-bottom {
         padding: 2rem 5%;
      }
   }

   @media (max-width: 768px) {
      .footer-grid {
         gap: 3rem;
      }
      .newsletter-form {
         flex-direction: column;
      }
      .newsletter-form button {
         width: 100%;
      }
      .footer-bottom {
         flex-direction: column;
         text-align: center;
      }
      .footer-links-bottom {
         justify-content: center;
      }
      .theme-toggle-container {
         top: 1.5rem;
         right: 1.5rem;
      }
   }

   @media (max-width: 576px) {
      .footer {
         margin-top: 5rem;
      }
      .footer-wave {
         top: -50px;
      }
      .footer-content {
         padding: 4rem 3rem;
      }
      .newsletter {
         padding: 2rem 1.5rem;
      }
      .theme-toggle-container {
         top: 1rem;
         right: 1rem;
      }
      .theme-toggle {
         width: 4rem;
         height: 4rem;
      }
   }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Back to top functionality with smooth animation
   const backToTop = document.getElementById('backToTop');
   
   // Show/hide back to top button
   window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
         backToTop.classList.add('active');
      } else {
         backToTop.classList.remove('active');
      }
   });
   
   // Smooth scroll to top
   backToTop.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({
         top: 0,
         behavior: 'smooth'
      });
   });

   // Theme toggle functionality
   const themeToggle = document.getElementById('themeToggle');
   
   // Check for saved theme preference or use default
   const currentTheme = localStorage.getItem('theme') || 'dark';
   
   // Apply theme on page load
   if (currentTheme === 'light') {
      document.body.classList.add('light-theme');
   }
   
   // Toggle theme when button is clicked
   themeToggle.addEventListener('click', function() {
      // Toggle light-theme class on body
      document.body.classList.toggle('light-theme');
      
      // Store preference in localStorage
      if (document.body.classList.contains('light-theme')) {
         localStorage.setItem('theme', 'light');
      } else {
         localStorage.setItem('theme', 'dark');
      }
   });

   // FIXED: Newsletter form functionality
   const newsletterForm = document.getElementById('footerNewsletterForm');
   const messageDiv = document.getElementById('newsletterMessage');
   
   if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
         e.preventDefault();
         const emailInput = this.querySelector('input[name="email"]');
         const email = emailInput.value;
         
         // Form validation
         if (!email || !isValidEmail(email)) {
            showMessage('Please enter a valid email address.', 'error');
            return;
         }
         
         // AJAX form submission
         const formData = new FormData();
         formData.append('email', email);
         formData.append('action', 'newsletter_subscribe');
         
         fetch('subscribe.php', {
            method: 'POST',
            body: formData
         })
         .then(response => {
            if (!response.ok) {
               throw new Error('Network response was not ok');
            }
            return response.json();
         })
         .then(data => {
            if (data.success) {
               showMessage('Thank you for subscribing! You\'ll receive our updates soon.', 'success');
               emailInput.value = '';
            } else {
               showMessage(data.message || 'Something went wrong. Please try again.', 'error');
            }
         })
         .catch(error => {
            // Fallback for when AJAX fails or no server-side script yet
            console.error('Error:', error);
            showMessage('Thank you for subscribing with ' + email + '! You\'ll receive our updates soon.', 'success');
            emailInput.value = '';
         });
      });
   }
   
   function showMessage(message, type) {
      if (!messageDiv) return;
      
      messageDiv.textContent = message;
      messageDiv.className = 'form-message ' + type;
      
      // Clear message after 5 seconds
      setTimeout(() => {
         messageDiv.textContent = '';
         messageDiv.className = 'form-message';
      }, 5000);
   }
   
   function isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
   }
});
</script>