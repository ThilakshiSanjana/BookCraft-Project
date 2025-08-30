

<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $user_type = $_POST['user_type'];

   $select_users = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'") or die('query failed');

   if(mysqli_num_rows($select_users) > 0){
      $message[] = 'user already exist!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         mysqli_query($conn, "INSERT INTO users(name, email, password, user_type) VALUES('$name', '$email', '$cpass', '$user_type')") or die('query failed');
         $message[] = 'registered successfully!';
         header('location:login.php');
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
   <title>BookCraft - Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
   
   <!-- GSAP CDN -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
     :root {
       --primary: #8B4513;
       --secondary: #D2B48C;
       --accent: #CD853F;
       --light: #F5F5DC;
       --dark: #3A2718;
       --paper: #FFFAF0;
     }
     
     * {
       margin: 0;
       padding: 0;
       box-sizing: border-box;
     }
     
     body {
       font-family: 'Montserrat', sans-serif;
       background-color: var(--light);
       color: var(--dark);
       min-height: 100vh;
       overflow-x: hidden;
       background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj4KPHBhdGggZD0iTTU5LjksMzBjMCwxNi41LTEzLjQsMzAtMzAsMzBDMTMuNCw2MCwwLDQ2LjYsMCwzMEMwLDEzLjQsMTMuNCwwLDMwLDBDNDYuNiwwLDU5LjksMTMuNCw1OS45LDMweiIgZmlsbC1vcGFjaXR5PSIwLjAyIiBmaWxsPSIjOEI0NTEzIi8+Cjwvc3ZnPg==');
       font-size: 16px;
     }
     
     .library-scene {
       position: fixed;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       perspective: 1500px;
       pointer-events: none;
       z-index: -1;
     }
     
     .bookshelf {
       position: absolute;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       transform-style: preserve-3d;
       opacity: 0.5;
     }
     
     .book {
       position: absolute;
       width: 30px;
       height: 150px;
       background-color: var(--primary);
       border-radius: 2px;
       box-shadow: -3px 0 5px rgba(0,0,0,0.2);
       transform-style: preserve-3d;
       opacity: 0;
     }
     
     .book:before {
       content: '';
       position: absolute;
       top: 0;
       left: 0;
       width: 30px;
       height: 150px;
       background: linear-gradient(to right, rgba(255,255,255,0.1), rgba(0,0,0,0.2));
       transform: rotateY(90deg) translateZ(15px) translateX(15px);
     }
     
     .book-spine {
       position: absolute;
       top: 10px;
       left: 5px;
       width: 20px;
       height: 130px;
       background-color: rgba(255,255,255,0.1);
       border-radius: 1px;
     }
     
     .main-container {
       width: 100%;
       max-width: 1200px;
       margin: 0 auto;
       padding: 20px;
       display: flex;
       justify-content: center;
       align-items: center;
       min-height: 100vh;
       position: relative;
       z-index: 1;
     }
     
     .register-container {
       display: flex;
       width: 100%;
       max-width: 1000px;
       box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);
       border-radius: 15px;
       overflow: hidden;
       background-color: var(--paper);
       background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj4KPHBhdGggZD0iTTAgMGg0MHY0MEgwVjB6IiBmaWxsLW9wYWNpdHk9IjAuMDUiIGZpbGw9IiNEMkI0OEMiLz4KPC9zdmc+');
       opacity: 0;
       transform: scale(0.95);
     }
     
     .register-image {
       flex: 1;
       background-image: url('https://images.unsplash.com/photo-1520467795206-62e33627e6ce?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');
       background-size: cover;
       background-position: center;
       position: relative;
       min-height: 500px;
     }
     
     .register-image:after {
       content: '';
       position: absolute;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       background: linear-gradient(rgba(139, 69, 19, 0.6), rgba(139, 69, 19, 0.9));
     }
     
     .quote-box {
       position: absolute;
       top: 50%;
       left: 50%;
       transform: translate(-50%, -50%);
       z-index: 2;
       text-align: center;
       width: 80%;
       color: var(--light);
     }
     
     .quote-box h2 {
       font-family: 'Libre Baskerville', serif;
       font-size: 2.5rem;
       margin-bottom: 20px;
       letter-spacing: 1px;
       opacity: 0;
       transform: translateY(20px);
       text-shadow: 0 2px 4px rgba(0,0,0,0.2);
     }
     
     .quote-box p {
       font-size: 1.2rem;
       line-height: 1.6;
       font-style: italic;
       margin-bottom: 20px;
       opacity: 0;
       transform: translateY(20px);
     }
     
     .book-divider {
       display: flex;
       align-items: center;
       justify-content: center;
       opacity: 0;
       margin: 25px 0;
     }
     
     .book-divider span {
       width: 40px;
       height: 3px;
       background-color: var(--light);
     }
     
     .book-divider i {
       margin: 0 15px;
       font-size: 1.2rem;
     }
     
     .form-side {
       flex: 1;
       padding: 50px;
       position: relative;
       overflow: hidden;
     }
     
     .paper-texture {
       position: absolute;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       opacity: 0.05;
       z-index: -1;
       background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj4KPHBhdGggZD0iTTAgMGgyMHYyMEgwVjB6IiBmaWxsLW9wYWNpdHk9IjAuMDgiIGZpbGw9IiM4QjQ1MTMiLz4KPC9zdmc+');
     }
     
     .register-header {
       text-align: center;
       margin-bottom: 30px;
     }
     
     .logo {
       margin-bottom: 20px;
       transform: scale(0);
     }
     
     .logo i {
       font-size: 3rem;
       color: var(--primary);
     }
     
     .register-header h3 {
       font-family: 'Libre Baskerville', serif;
       font-size: 2.2rem;
       color: var(--dark);
       margin-bottom: 15px;
       opacity: 0;
     }
     
     .register-header p {
       color: #555;
       font-size: 1.1rem;
       opacity: 0;
       line-height: 1.5;
     }
     
     .form-group {
       margin-bottom: 25px;
       position: relative;
       opacity: 0;
       transform: translateX(20px);
     }
     
     .form-group label {
       display: block;
       margin-bottom: 10px;
       font-size: 1.1rem;
       color: var(--dark);
       font-weight: 500;
     }
     
     .form-input {
       width: 100%;
       padding: 15px;
       border: 2px solid #ddd;
       border-radius: 8px;
       font-size: 1.2rem;
       background-color: rgba(255,255,255,0.9);
       transition: all 0.3s;
     }
     
     .form-input:focus {
       border-color: var(--primary);
       box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.2);
       outline: none;
     }
     
     .form-select {
       width: 100%;
       padding: 15px;
       border: 2px solid #ddd;
       border-radius: 8px;
       font-size: 1.2rem;
       background-color: rgba(255,255,255,0.9);
       transition: all 0.3s;
       appearance: none;
       background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="8"><path d="M0 0l7 8 7-8z" fill="%238B4513"/></svg>');
       background-repeat: no-repeat;
       background-position: right 15px center;
       background-size: 14px;
     }
     
     .form-select:focus {
       border-color: var(--primary);
       box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.2);
       outline: none;
     }
     
     .submit-btn {
       width: 100%;
       padding: 16px;
       background-color: var(--primary);
       color: white;
       border: none;
       border-radius: 8px;
       font-size: 1.2rem;
       font-weight: 600;
       cursor: pointer;
       transition: all 0.3s;
       margin-bottom: 25px;
       opacity: 0;
       position: relative;
       overflow: hidden;
       letter-spacing: 0.5px;
     }
     
     .submit-btn:before {
       content: '';
       position: absolute;
       top: 0;
       left: 0;
       width: 0%;
       height: 100%;
       background-color: rgba(255,255,255,0.1);
       transition: width 0.3s ease;
     }
     
     .submit-btn:hover:before {
       width: 100%;
     }
     
     .submit-btn:hover {
       background-color: #7a3b10;
     }
     
     .login-text {
       text-align: center;
       font-size: 1.1rem;
       color: #555;
       opacity: 0;
     }
     
     .login-text a {
       color: var(--primary);
       font-weight: 600;
       text-decoration: none;
     }
     
     .login-text a:hover {
       text-decoration: underline;
     }
     
     .message {
       background-color: rgba(220, 53, 69, 0.1);
       color: #dc3545;
       padding: 15px;
       border-radius: 8px;
       margin-bottom: 25px;
       display: flex;
       justify-content: space-between;
       align-items: center;
       opacity: 0;
       transform: translateY(-10px);
       font-size: 1.1rem;
       border-left: 4px solid #dc3545;
     }
     
     .message i {
       cursor: pointer;
       padding: 5px;
       font-size: 1.1rem;
     }
     
     .success-message {
       background-color: rgba(40, 167, 69, 0.1);
       color: #28a745;
       border-left: 4px solid #28a745;
     }
     
     .page-corner {
       position: absolute;
       top: 0;
       right: 0;
       width: 40px;
       height: 40px;
       background-color: var(--paper);
       transform: rotate(-90deg) translate(28.5px, 28.5px);
       z-index: 1;
       border-radius: 0 0 0 8px;
       box-shadow: -2px 2px 5px rgba(0,0,0,0.1);
     }
     
     /* Password visibility toggle styles */
     .password-container {
       position: relative;
     }
     
     .password-toggle {
       position: absolute;
       right: 15px;
       top: 50%;
       transform: translateY(-50%);
       cursor: pointer;
       color: #8B4513;
       opacity: 0.7;
       font-size: 1.2rem;
       transition: all 0.2s;
     }
     
     .password-toggle:hover {
       opacity: 1;
     }
     
     @media (max-width: 992px) {
       body {
         font-size: 15px;
       }
       
       .form-side {
         padding: 40px;
       }
       
       .quote-box h2 {
         font-size: 2.2rem;
       }
     }
     
     @media (max-width: 768px) {
       .register-container {
         flex-direction: column;
       }
       
       .register-image {
         min-height: 250px;
       }
       
       .form-side {
         padding: 30px 25px;
       }
       
       .quote-box h2 {
         font-size: 2rem;
       }
       
       .quote-box p {
         font-size: 1.1rem;
       }
       
       .register-header h3 {
         font-size: 2rem;
       }
     }
     
     @media (max-width: 576px) {
       body {
         font-size: 14px;
       }
       
       .form-input, 
       .form-select,
       .submit-btn {
         font-size: 1.1rem;
         padding: 12px;
       }
       
       .form-group label {
         font-size: 1rem;
       }
       
       .quote-box h2 {
         font-size: 1.8rem;
       }
       
       .register-header h3 {
         font-size: 1.8rem;
       }
     }
   </style>
</head>
<body>

<div class="library-scene">
  <div class="bookshelf" id="bookshelf"></div>
</div>

<div class="main-container">
  <div class="register-container">
    <div class="page-corner"></div>
    
    <div class="register-image">
      <div class="quote-box">
        <h2>Join BookCraft</h2>
        <div class="book-divider">
          <span></span>
          <i class="fas fa-feather-alt"></i>
          <span></span>
        </div>
        <p>"Once you learn to read, you will be forever free." — Frederick Douglass</p>
      </div>
    </div>
    
    <div class="form-side">
      <div class="paper-texture"></div>
      
      <?php
      if(isset($message)){
         foreach($message as $message){
            if(strpos($message, 'successfully') !== false) {
               echo '
               <div class="message success-message">
                  <span>'.$message.'</span>
                  <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
               </div>
               ';
            } else {
               echo '
               <div class="message">
                  <span>'.$message.'</span>
                  <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
               </div>
               ';
            }
         }
      }
      ?>
      
      <div class="register-header">
        <div class="logo">
          <i class="fas fa-book-open"></i>
        </div>
        <h3>Create Account</h3>
        <p>Become a part of our BookCraft community</p>
      </div>
      
      <form action="" method="post">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" class="form-input" placeholder="Enter your full name" required>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-container">
            <input type="password" id="password" name="password" class="form-input" placeholder="Create a secure password" required>
            <i class="password-toggle fas fa-eye" id="togglePassword"></i>
          </div>
        </div>
        
        <div class="form-group">
          <label for="cpassword">Confirm Password</label>
          <div class="password-container">
            <input type="password" id="cpassword" name="cpassword" class="form-input" placeholder="Confirm your password" required>
            <i class="password-toggle fas fa-eye" id="toggleCPassword"></i>
          </div>
        </div>
        
        <div class="form-group">
          <label for="user_type">Account Type</label>
          <select id="user_type" name="user_type" class="form-select">
            <option value="user">Customer</option>
           
          </select>
        </div>
        
        <button type="submit" name="submit" class="submit-btn">Create Account</button>
        
        <div class="login-text">
          <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Create 3D books for the bookshelf
    const bookshelf = document.getElementById('bookshelf');
    const bookColors = [
      '#8B4513', '#A0522D', '#CD853F', '#D2691E', 
      '#8B0000', '#B22222', '#4B0082', '#483D8B',
      '#2E8B57', '#006400', '#3CB371', '#20B2AA'
    ];
    
    for (let i = 0; i < 40; i++) {
      const book = document.createElement('div');
      book.className = 'book';
      
      // Random position
      const x = Math.random() * 100;
      const y = Math.random() * 100;
      const z = Math.random() * 200 - 400;
      
      // Random rotation
      const rotateY = Math.random() * 20 - 10;
      
      // Random color
      const randomColor = bookColors[Math.floor(Math.random() * bookColors.length)];
      book.style.backgroundColor = randomColor;
      
      // Random size
      const height = 120 + Math.random() * 60;
      const width = 20 + Math.random() * 15;
      book.style.height = `${height}px`;
      book.style.width = `${width}px`;
      
      // Position and rotation
      book.style.transform = `translate3d(${x}vw, ${y}vh, ${z}px) rotateY(${rotateY}deg)`;
      
      // Create book spine
      const spine = document.createElement('div');
      spine.className = 'book-spine';
      spine.style.height = `${height - 20}px`;
      spine.style.width = `${width - 10}px`;
      
      book.appendChild(spine);
      bookshelf.appendChild(book);
    }
    
    // Password visibility toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const toggleCPassword = document.getElementById('toggleCPassword');
    const passwordInput = document.getElementById('password');
    const cPasswordInput = document.getElementById('cpassword');
    
    togglePassword.addEventListener('click', function() {
      // Toggle type attribute
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Toggle icon
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
      
      // Add subtle animation
      gsap.to(this, {
        rotate: type === 'password' ? 0 : 180,
        duration: 0.3,
        ease: 'power2.out'
      });
    });
    
    toggleCPassword.addEventListener('click', function() {
      // Toggle type attribute
      const type = cPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      cPasswordInput.setAttribute('type', type);
      
      // Toggle icon
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
      
      // Add subtle animation
      gsap.to(this, {
        rotate: type === 'password' ? 0 : 180,
        duration: 0.3,
        ease: 'power2.out'
      });
    });
    
    // GSAP Animations
    const tl = gsap.timeline({defaults: {ease: 'power3.out'}});
    
    // Animate books
    tl.to('.book', {
      opacity: 1,
      stagger: {
        from: 'random',
        amount: 2
      },
      duration: 1
    });
    
    // Register container animation
    tl.to('.register-container', {
      opacity: 1,
      scale: 1,
      duration: 1
    }, '-=0.5');
    
    // Logo animation
    tl.to('.logo', {
      scale: 1,
      duration: 1,
      ease: 'elastic.out(1, 0.5)'
    }, '-=0.5');
    
    // Register header animations
    tl.to('.register-header h3, .register-header p', {
      opacity: 1,
      stagger: 0.2,
      duration: 0.8
    }, '-=0.6');
    
    // Form elements animations
    tl.to('.form-group', {
      opacity: 1,
      x: 0,
      stagger: 0.1,
      duration: 0.8
    }, '-=0.4');
    
    tl.to('.submit-btn, .login-text', {
      opacity: 1,
      stagger: 0.2,
      duration: 0.8
    }, '-=0.4');
    
    // Quote box animations
    tl.to('.quote-box h2, .book-divider, .quote-box p', {
      opacity: 1,
      y: 0,
      stagger: 0.3,
      duration: 0.8
    }, '-=1.2');
    
    // Error message animation if present
    if (document.querySelector('.message')) {
      gsap.to('.message', {
        opacity: 1,
        y: 0,
        duration: 0.5,
        ease: 'back.out(1.7)'
      });
    }
    
    // Book parallax effect
    document.addEventListener('mousemove', function(e) {
      const mouseX = e.clientX / window.innerWidth - 0.5;
      const mouseY = e.clientY / window.innerHeight - 0.5;
      
      gsap.to('.book', {
        x: mouseX * 20,
        y: mouseY * 20,
        duration: 1,
        ease: 'power1.out'
      });
      
      gsap.to('.bookshelf', {
        rotationY: mouseX * 5,
        rotationX: -mouseY * 5,
        duration: 1,
        ease: 'power1.out'
      });
    });
    
    // Button hover animation
    const button = document.querySelector('.submit-btn');
    button.addEventListener('mouseenter', function() {
      gsap.to(this, {
        y: -3,
        duration: 0.3,
        boxShadow: '0 6px 15px rgba(139, 69, 19, 0.3)'
      });
    });
    
    button.addEventListener('mouseleave', function() {
      gsap.to(this, {
        y: 0,
        duration: 0.3,
        boxShadow: '0 0 0 rgba(139, 69, 19, 0)'
      });
    });
    
    // Form input focus animations
    const inputs = document.querySelectorAll('.form-input, .form-select');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        gsap.to(this, {
          boxShadow: '0 0 0 3px rgba(139, 69, 19, 0.2)',
          duration: 0.3
        });
      });
      
      input.addEventListener('blur', function() {
        if (!this.value) {
          gsap.to(this, {
            boxShadow: 'none',
            duration: 0.3
          });
        }
      });
    });
  });
</script>

</body>
</html>