<?php

include 'config.php';
session_start();

$message = array();
$valid_token = false;
$email = '';
$token = '';
$csrf_token = bin2hex(random_bytes(32)); 


$_SESSION['csrf_token'] = $csrf_token;

if(isset($_GET['email']) && isset($_GET['token'])) {
    $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $token = htmlspecialchars($_GET['token']); // Sanitize token
    
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expires > NOW()");
    if($stmt) {
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $valid_token = true;
        } else {
            $message[] = 'Invalid or expired reset link! Please request a new one.';
        }
        $stmt->close();
    } else {
        $message[] = 'Database error. Please try again later.';
    }
} else {
    $message[] = 'Invalid reset link! Please request a new one.';
}

// Process form submission
if(isset($_POST['submit']) && $valid_token) {
    // Verify CSRF token for security
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message[] = 'Security verification failed. Please try again.';
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Enhanced password validation
        if(strlen($password) < 8) {
            $message[] = 'Password must be at least 8 characters long!';
        } elseif(!preg_match('/[A-Z]/', $password)) {
            $message[] = 'Password must contain at least one uppercase letter!';
        } elseif(!preg_match('/[0-9]/', $password)) {
            $message[] = 'Password must contain at least one number!';
        } elseif(!preg_match('/[^A-Za-z0-9]/', $password)) {
            $message[] = 'Password must contain at least one special character!';
        } elseif($password !== $confirm_password) {
            $message[] = 'Passwords do not match!';
        } else {
            // Hash the password with stronger algorithm
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Update password and clear reset token
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expires = NULL WHERE email = ?");
            if($update_stmt) {
                $update_stmt->bind_param("ss", $hashed_password, $email);
                
                if($update_stmt->execute()) {
                    $message[] = 'Password has been reset successfully! You can now <a href="login.php">login</a> with your new password.';
                    $valid_token = false; // Prevent form from showing after successful reset
                    
                    // Log the successful password change (optional security feature)
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $log_stmt = $conn->prepare("INSERT INTO security_logs (email, action, ip_address, user_agent, created_at) VALUES (?, 'password_reset', ?, ?, NOW())");
                    if($log_stmt) {
                        $log_stmt->bind_param("sss", $email, $ip, $user_agent);
                        $log_stmt->execute();
                        $log_stmt->close();
                    }
                } else {
                    $message[] = 'Failed to update password. Please try again.';
                }
                $update_stmt->close();
            } else {
                $message[] = 'System error. Please try again later.';
            }
        }
        
        // Regenerate CSRF token after form submission for added security
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf_token = $_SESSION['csrf_token'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BookCraft - Reset Password</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
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
       --success: #28a745;
       --danger: #dc3545;
       --warning: #ffc107;
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
     
     .reset-password-container {
       display: flex;
       width: 100%;
       max-width: 900px;
       box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);
       border-radius: 15px;
       overflow: hidden;
       background-color: var(--paper);
       background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj4KPHBhdGggZD0iTTAgMGg0MHY0MEgwVjB6IiBmaWxsLW9wYWNpdHk9IjAuMDUiIGZpbGw9IiNEMkI0OEMiLz4KPC9zdmc+');
       opacity: 0;
       transform: scale(0.95);
     }
     
     .image-side {
       flex: 1;
       background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80');
       background-size: cover;
       background-position: center;
       position: relative;
       min-height: 500px;
     }
     
     .image-side:after {
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
       font-size: 1.5rem;
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
     
     .form-header {
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
     
     .form-header h3 {
       font-family: 'Libre Baskerville', serif;
       font-size: 2.5rem;
       color: var(--dark);
       margin-bottom: 15px;
       opacity: 0;
     }
     
     .form-header p {
       color: #555;
       font-size: 1.5rem;
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
       font-size: 1.5rem;
       color: var(--dark);
       font-weight: 500;
     }
     
     .form-input {
       width: 100%;
       padding: 15px;
       border: 2px solid #ddd;
       border-radius: 8px;
       font-size: 1.3rem;
       background-color: rgba(255,255,255,0.9);
       transition: all 0.3s;
     }
     
     .form-input:focus {
       border-color: var(--primary);
       box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.2);
       outline: none;
     }
     
     .password-input-wrapper {
       position: relative;
     }
     
     .toggle-password {
       position: absolute;
       right: 15px;
       top: 50%;
       transform: translateY(-50%);
       cursor: pointer;
       color: #666;
       font-size: 1.2rem;
     }
     
     .password-strength {
       margin-top: 10px;
       height: 5px;
       background-color: #eee;
       border-radius: 3px;
       overflow: hidden;
     }
     
     .password-strength-meter {
       height: 100%;
       width: 0%;
       transition: width 0.3s, background-color 0.3s;
     }
     
     .password-feedback {
       margin-top: 8px;
       font-size: 0.9rem;
       color: #666;
     }
     
     /* Password requirements checklist */
     .password-requirements {
       margin-top: 12px;
       font-size: 0.9rem;
       color: #666;
     }
     
     .requirement {
       display: flex;
       align-items: center;
       margin-bottom: 4px;
     }
     
     .requirement i {
       margin-right: 6px;
       font-size: 0.8rem;
       width: 14px;
       text-align: center;
     }
     
     .requirement.valid i {
       color: var(--success);
     }
     
     .requirement.invalid i {
       color: #999;
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
       display: flex;
       justify-content: center;
       align-items: center;
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
     
     /* Loading spinner */
     .spinner {
       display: none;
       margin-left: 10px;
       width: 18px;
       height: 18px;
       border: 2px solid rgba(255,255,255,0.3);
       border-radius: 50%;
       border-top-color: #fff;
       animation: spin 0.8s linear infinite;
     }
     
     @keyframes spin {
       to { transform: rotate(360deg); }
     }
     
     .back-link {
       text-align: center;
       font-size: 1.1rem;
       color: #555;
       opacity: 0;
     }
     
     .back-link a {
       color: var(--primary);
       font-weight: 600;
       text-decoration: none;
       transition: all 0.2s;
     }
     
     .back-link a:hover {
       text-decoration: underline;
       color: #7a3b10;
     }
     
     .message {
       padding: 15px;
       border-radius: 8px;
       margin-bottom: 25px;
       display: flex;
       justify-content: space-between;
       align-items: flex-start;
       opacity: 0;
       transform: translateY(-10px);
       font-size: 1.1rem;
     }
     
     .message.error {
       background-color: rgba(220, 53, 69, 0.1);
       color: var(--danger);
       border-left: 4px solid var(--danger);
     }
     
     .message.success {
       background-color: rgba(40, 167, 69, 0.1);
       color: var(--success);
       border-left: 4px solid var(--success);
     }
     
     .message i {
       cursor: pointer;
       padding: 5px;
       font-size: 1.1rem;
     }
     
     .message span {
       flex: 1;
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
     
     .page-corner:before {
       content: '';
       position: absolute;
       top: 0;
       left: 0;
       width: 40px;
       height: 40px;
       background-color: rgba(0,0,0,0.05);
       border-radius: 0 0 0 8px;
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
       .reset-password-container {
         flex-direction: column;
       }
       
       .image-side {
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
       
       .form-header h3 {
         font-size: 2rem;
       }
     }
     
     @media (max-width: 576px) {
       body {
         font-size: 14px;
       }
       
       .form-input, 
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
       
       .form-header h3 {
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
  <div class="reset-password-container">
    <div class="page-corner"></div>
    
    <div class="image-side">
      <div class="quote-box">
        <h2>Reset Your Password</h2>
        <div class="book-divider">
          <span></span>
          <i class="fas fa-unlock-alt"></i>
          <span></span>
        </div>
        <p>"A reader lives a thousand lives before he dies. The man who never reads lives only one." — George R.R. Martin</p>
      </div>
    </div>
    
    <div class="form-side">
      <div class="paper-texture"></div>
      
      <?php
      if(isset($message) && !empty($message)){
         foreach($message as $msg){
            $class = (strpos($msg, 'not match') !== false || 
                     strpos($msg, 'Invalid') !== false || 
                     strpos($msg, 'error') !== false || 
                     strpos($msg, 'Failed') !== false || 
                     strpos($msg, 'must') !== false) ? 'error' : 'success';
            echo '
            <div class="message '.$class.'">
               <span>'.$msg.'</span>
               <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
            </div>
            ';
         }
      }
      ?>
      
      <div class="form-header">
        <div class="logo">
          <i class="fas fa-book-open"></i>
        </div>
        <h3>Create New Password</h3>
        <p>Please enter your new password below</p>
      </div>
      
      <?php if($valid_token): ?>
      <form action="forgot_password.php?email=<?php echo urlencode($email); ?>&token=<?php echo urlencode($token); ?>" method="post" id="passwordResetForm">
        <!-- CSRF token for security -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="form-group">
          <label for="password">New Password</label>
          <div class="password-input-wrapper">
            <input type="password" id="password" name="password" class="form-input" 
                   placeholder="Enter your new password" required minlength="8" autofocus>
            <i class="toggle-password fas fa-eye-slash" onclick="togglePasswordVisibility('password')"></i>
          </div>
          <div class="password-strength">
            <div class="password-strength-meter" id="passwordStrengthMeter"></div>
          </div>
          <div class="password-feedback" id="passwordFeedback">Password must be at least 8 characters</div>
          
          <!-- Password requirements checklist -->
          <div class="password-requirements">
            <div class="requirement" id="length"><i class="fas fa-circle"></i> At least 8 characters</div>
            <div class="requirement" id="uppercase"><i class="fas fa-circle"></i> At least one uppercase letter</div>
            <div class="requirement" id="number"><i class="fas fa-circle"></i> At least one number</div>
            <div class="requirement" id="special"><i class="fas fa-circle"></i> At least one special character</div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="password-input-wrapper">
            <input type="password" id="confirm_password" name="confirm_password" 
                   class="form-input" placeholder="Confirm your new password" required>
            <i class="toggle-password fas fa-eye-slash" onclick="togglePasswordVisibility('confirm_password')"></i>
          </div>
          <div class="password-feedback" id="passwordMatchFeedback"></div>
        </div>
        
        <button type="submit" name="submit" class="submit-btn" id="submitBtn">
          <span>Reset Password</span>
          <div class="spinner" id="loadingSpinner"></div>
        </button>
        
        <div class="back-link">
          <p>Remember your password? <a href="login.php">Back to Login</a></p>
        </div>
      </form>
      <?php else: ?>
        <div class="back-link" style="opacity: 1; margin-top: 20px;">
          <p><a href="login.php">Back to Login</a></p>
        </div>
      <?php endif; ?>
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
    
    // GSAP Animations
    if (typeof gsap !== 'undefined') {
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
      
      // Container animation
      tl.to('.reset-password-container', {
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
      
      // Header animations
      tl.to('.form-header h3, .form-header p', {
        opacity: 1,
        stagger: 0.2,
        duration: 0.8
      }, '-=0.6');
      
      // Form elements animations
      tl.to('.form-group', {
        opacity: 1,
        x: 0,
        stagger: 0.2,
        duration: 0.8
      }, '-=0.4');
      
      tl.to('.submit-btn, .back-link', {
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
      
      // Error/success message animation if present
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
      if (button) {
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
      }
      
      // Form input focus animations
      const inputs = document.querySelectorAll('.form-input');
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
    } else {
      // Fallback if GSAP fails to load
      document.querySelector('.reset-password-container').style.opacity = 1;
      document.querySelector('.reset-password-container').style.transform = 'scale(1)';
      
      const elements = document.querySelectorAll('.form-group, .submit-btn, .back-link, .logo, .form-header h3, .form-header p, .quote-box h2, .book-divider, .quote-box p, .message');
      elements.forEach(el => {
        el.style.opacity = 1;
        el.style.transform = 'none';
      });
    }
    
    // Password toggle visibility
    window.togglePasswordVisibility = function(inputId) {
      const input = document.getElementById(inputId);
      const icon = input.nextElementSibling;
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      }
    };
    
    // Enhanced password strength meter with requirements checking
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrengthMeter = document.getElementById('passwordStrengthMeter');
    const passwordFeedback = document.getElementById('passwordFeedback');
    const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');
    
    // Password requirement elements
    const lengthReq = document.getElementById('length');
    const upperReq = document.getElementById('uppercase');
    const numberReq = document.getElementById('number');
    const specialReq = document.getElementById('special');
    
    if (passwordInput) {
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let feedback = '';
        
        // Check requirements
        const hasLength = password.length >= 8;
        const hasUpper = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[^A-Za-z0-9]/.test(password);
        
        // Update requirement indicators
        updateRequirement(lengthReq, hasLength);
        updateRequirement(upperReq, hasUpper);
        updateRequirement(numberReq, hasNumber);
        updateRequirement(specialReq, hasSpecial);
        
        // Calculate strength
        if (password.length === 0) {
          passwordStrengthMeter.style.width = '0%';
          passwordStrengthMeter.style.backgroundColor = '#eee';
          passwordFeedback.textContent = 'Password must be at least 8 characters';
          return;
        }
        
        if (hasLength) strength += 25;
        if (hasUpper) strength += 25;
        if (hasNumber) strength += 25;
        if (hasSpecial) strength += 25;
        
        // Update strength meter
        passwordStrengthMeter.style.width = `${strength}%`;
        
        if (strength < 50) {
          passwordStrengthMeter.style.backgroundColor = '#dc3545';
          feedback = 'Weak';
        } else if (strength < 100) {
          passwordStrengthMeter.style.backgroundColor = '#ffc107';
          feedback = 'Moderate';
        } else {
          passwordStrengthMeter.style.backgroundColor = '#28a745';
          feedback = 'Strong';
        }
        
        passwordFeedback.textContent = feedback;
        
        // Check if passwords match
        if (confirmPasswordInput.value) {
          checkPasswordMatch();
        }
      });
    }
    
    // Update requirement indicator
    function updateRequirement(element, isValid) {
      if (isValid) {
        element.classList.add('valid');
        element.classList.remove('invalid');
        element.querySelector('i').className = 'fas fa-check-circle';
      } else {
        element.classList.add('invalid');
        element.classList.remove('valid');
        element.querySelector('i').className = 'fas fa-circle';
      }
    }
    
    // Check if passwords match
    function checkPasswordMatch() {
      if (confirmPasswordInput.value === passwordInput.value) {
        passwordMatchFeedback.textContent = 'Passwords match';
        passwordMatchFeedback.style.color = '#28a745';
      } else {
        passwordMatchFeedback.textContent = 'Passwords do not match';
        passwordMatchFeedback.style.color = '#dc3545';
      }
    }
    
    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Form validation
    const resetForm = document.getElementById('passwordResetForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    if (resetForm) {
      resetForm.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        // Enhanced validation
        let isValid = true;
        let errorMessage = '';
        
        if (password.length < 8) {
          isValid = false;
          errorMessage = 'Password must be at least 8 characters long!';
        } else if (!/[A-Z]/.test(password)) {
          isValid = false;
          errorMessage = 'Password must contain at least one uppercase letter!';
        } else if (!/[0-9]/.test(password)) {
          isValid = false;
          errorMessage = 'Password must contain at least one number!';
        } else if (!/[^A-Za-z0-9]/.test(password)) {
          isValid = false;
          errorMessage = 'Password must contain at least one special character!';
        } else if (password !== confirmPassword) {
          isValid = false;
          errorMessage = 'Passwords do not match!';
        }
        
        if (!isValid) {
          e.preventDefault();
          
          // Create message element
          const messageDiv = document.createElement('div');
          messageDiv.className = 'message error';
          messageDiv.innerHTML = `
            <span>${errorMessage}</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
          `;
          
          // Insert before form header
          const formHeader = document.querySelector('.form-header');
          resetForm.parentNode.insertBefore(messageDiv, formHeader);
          
          // Animate message
          if (typeof gsap !== 'undefined') {
            gsap.from(messageDiv, {
              y: -20,
              opacity: 0,
              duration: 0.5
            });
          } else {
            messageDiv.style.opacity = 1;
          }
          
          // Scroll to top if needed
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          // Show loading spinner
          loadingSpinner.style.display = 'block';
          submitBtn.querySelector('span').textContent = 'Processing...';
          submitBtn.disabled = true;
        }
      });
    }
  });
</script>

</body>
</html>