<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin_name'])){
   header('location:login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Panel</title>
   
   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link -->
   <style>
      :root{
         --primary-color: #4834d4;
         --black: #333;
         --white: #fff;
         --light-color: #666;
         --border: .2rem solid var(--black);
         --box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         outline: none;
         border: none;
         text-decoration: none;
      }

      html {
         font-size: 62.5%;
         overflow-x: hidden;
      }

      body {
         background: #f7f7f7;
      }

      .message {
         position: sticky;
         top: 0;
         max-width: 1200px;
         margin: 0 auto;
         background-color: #fff;
         padding: 2rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         z-index: 1000;
         gap: 1.5rem;
         box-shadow: var(--box-shadow);
         margin-bottom: 2rem;
      }

      .message span {
         font-size: 1.7rem;
         color: var(--black);
      }

      .message i {
         cursor: pointer;
         color: var(--light-color);
         font-size: 2rem;
      }

      .message i:hover {
         color: var(--primary-color);
      }

      .header {
         position: sticky;
         top: 0;
         left: 0;
         right: 0;
         z-index: 1000;
         background-color: var(--white);
         box-shadow: var(--box-shadow);
      }

      .header .flex {
         display: flex;
         align-items: center;
         padding: 2rem;
         justify-content: space-between;
         position: relative;
         max-width: 1200px;
         margin: 0 auto;
      }

      .header .flex .logo {
         font-size: 2.5rem;
         color: var(--black);
         font-weight: bold;
      }

      .header .flex .logo span {
         color: var(--primary-color);
      }

      .header .flex .navbar a {
         margin: 0 1rem;
         font-size: 1.7rem;
         color: var(--black);
         text-transform: capitalize;
      }

      .header .flex .navbar a:hover {
         color: var(--primary-color);
         text-decoration: underline;
      }

      .header .flex .icons div {
         margin-left: 1.5rem;
         font-size: 2.5rem;
         cursor: pointer;
         color: var(--black);
      }

      .header .flex .icons div:hover {
         color: var(--primary-color);
      }

      .header .flex .account-box {
         position: absolute;
         top: 120%;
         right: 2rem;
         width: 30rem;
         box-shadow: var(--box-shadow);
         border: var(--border);
         background-color: var(--white);
         padding: 2rem;
         text-align: center;
         display: none;
         animation: fadeIn .2s linear;
      }

      .header .flex .account-box.active {
         display: block;
      }

      .header .flex .account-box p {
         font-size: 1.8rem;
         color: var(--light-color);
         margin-bottom: 1.5rem;
      }

      .header .flex .account-box p span {
         color: var(--black);
      }

      .header .flex .account-box .delete-btn {
         margin-top: 1rem;
         padding: 1rem 3rem;
         background-color: crimson;
         color: var(--white);
         font-size: 1.8rem;
         cursor: pointer;
         border-radius: .5rem;
         display: inline-block;
      }

      .header .flex .account-box .delete-btn:hover {
         background-color: var(--black);
      }

      .header .flex .account-box div {
         margin-top: 1.5rem;
         font-size: 1.6rem;
         color: var(--light-color);
      }

      .header .flex .account-box div a {
         color: orange;
      }

      .header .flex .account-box div a:hover {
         text-decoration: underline;
      }

      #menu-btn {
         display: none;
      }

      @keyframes fadeIn {
         0% {
            transform: translateY(1rem);
            opacity: 0;
         }
      }

      @media (max-width:991px) {
         html {
            font-size: 55%;
         }
      }

      @media (max-width:768px) {
         #menu-btn {
            display: inline-block;
         }

         .header .flex .navbar {
            position: absolute;
            top: 99%;
            left: 0;
            right: 0;
            background-color: var(--white);
            border-top: var(--border);
            border-bottom: var(--border);
            clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
         }

         .header .flex .navbar.active {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
         }

         .header .flex .navbar a {
            margin: 2rem;
            display: block;
         }
      }

      @media (max-width:450px) {
         html {
            font-size: 50%;
         }
      }
   </style>
</head>
<body>
   
<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">home</a>
         <a href="admin_products.php">products</a>
         <a href="admin_orders.php">orders</a>
         <a href="admin_users.php">users</a>
         <a href="admin_contacts.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="account-box">
         <p>username : <span><?php echo $_SESSION['admin_name']; ?></span></p>
         <p>email : <span><?php echo $_SESSION['admin_email']; ?></span></p>
         <a href="logout.php" class="delete-btn">logout</a>
         <div>new <a href="login.php">login</a> | <a href="register.php">register</a></div>
      </div>

   </div>

</header>

<script>
let navbar = document.querySelector('.navbar');
let accountBox = document.querySelector('.account-box');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   accountBox.classList.remove('active');
}

document.querySelector('#user-btn').onclick = () =>{
   accountBox.classList.toggle('active');
   navbar.classList.remove('active');
}

window.onscroll = () =>{
   navbar.classList.remove('active');
   accountBox.classList.remove('active');
}
</script>

</body>
</html>