<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About BookCraft</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <!-- GSAP and ScrollTrigger -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
   
   <!-- Three.js -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

   <style>
      :root {
         --primary: #27ae60;
         --secondary: #192a56;
         --light-color: #666;
         --white: #fff;
         --black: #333;
         --light-bg: #f5f5f5;
         --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
         --border: .1rem solid var(--light-color);
      }

      * {
         font-family: 'Poppins', sans-serif;
         transition: all 0.3s ease;
      }

      body {
         overflow-x: hidden;
      }

      .heading {
         background: url('data:image/jpeg;base64,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') no-repeat;
         background-size: cover;
         background-position: center;
         text-align: center;
         padding: 5rem 0;
         position: relative;
         overflow: hidden;
         height: 60vh;
      }

      .heading::before {
         content: '';
         position: absolute;
         top: 0; left: 0;
         width: 100%; height: 100%;
         background: rgba(175, 169, 169, 0.7);
      }

      .heading h3 {
         font-size: 4rem;
         color: var(--white);
         text-transform: uppercase;
         position: relative;
         margin-bottom: 1rem;
         text-shadow: 2px 2px 10px rgba(39, 12, 12, 0.91);
      }

      .heading p {
         font-size: 2.5rem;
         color: var(--white);
         position: relative;
      }

      .heading p a {
         color: var(--white);
         font-weight: 500;
      }

      .heading p a:hover {
         text-decoration: underline;
      }

      #book-canvas {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         z-index: 0;
      }

      .heading-content {
         position: relative;
         z-index: 1;
      }

      .title {
         text-align: center;
         margin-bottom: 3rem;
         text-transform: uppercase;
         color: var(--black);
         font-size: 3rem;
         padding: 1rem;
         position: relative;
      }

      .title::after {
         content: '';
         position: absolute;
         bottom: 0; left: 50%;
         transform: translateX(-50%);
         width: 10rem;
         height: .2rem;
         background: var(--primary);
      }

      .about .flex {
         display: flex;
         flex-wrap: wrap;
         gap: 3rem;
         align-items: center;
         max-width: 1200px;
         margin: 0 auto;
         padding: 5rem 2rem;
      }

      .about .flex .image {
         flex: 1 1 40rem;
         box-shadow: var(--box-shadow);
         border-radius: 1rem;
         overflow: hidden;
         transform: translateX(-100px);
         opacity: 0;
      }

      .about .flex .image img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.8s ease;
      }

      .about .flex .image:hover img {
         transform: scale(1.08);
      }

      .about .flex .content {
         flex: 1 1 40rem;
         padding: 2rem;
         transform: translateX(100px);
         opacity: 0;
      }

      .about .flex .content h3 {
         font-size: 2.8rem;
         color: var(--black);
         margin-bottom: 1.5rem;
         position: relative;
         padding-bottom: 1.5rem;
      }

      .about .flex .content h3::before {
         content: '';
         position: absolute;
         bottom: 0; left: 0;
         width: 8rem;
         height: .3rem;
         background: var(--primary);
      }

      .about .flex .content p {
         font-size: 1.6rem;
         padding: .5rem 0;
         line-height: 2;
         color: var(--light-color);
      }

      .btn {
         display: inline-block;
         margin-top: 1.5rem;
         padding: 1.2rem 3.5rem;
         font-size: 1.7rem;
         color: var(--white);
         border-radius: .5rem;
         background: var(--primary);
         cursor: pointer;
         text-decoration: none;
         text-transform: uppercase;
         letter-spacing: 1px;
         box-shadow: 0 .5rem 1rem rgba(39, 174, 96, .2);
         position: relative;
         overflow: hidden;
         z-index: 1;
      }

      .btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 0%;
         height: 100%;
         background: var(--secondary);
         z-index: -1;
         transition: width 0.4s ease;
      }

      .btn:hover::before {
         width: 100%;
      }

      .btn:hover {
         transform: translateY(-5px);
         box-shadow: 0 1rem 2rem rgba(39, 174, 96, .3);
      }

      .reviews,
      .authors {
         background: var(--light-bg);
         padding: 6rem 0;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
         gap: 2.5rem;
         max-width: 1200px;
         margin: 0 auto;
         padding: 2rem;
      }

      .reviews .box-container .box {
         background: var(--white);
         border-radius: 1rem;
         padding: 2.5rem;
         text-align: center;
         box-shadow: var(--box-shadow);
         transition: all 0.5s ease;
         transform: translateY(50px);
         opacity: 0;
      }

      .reviews .box-container .box:hover {
         transform: translateY(-10px) scale(1.03);
         box-shadow: 0 1rem 2rem rgba(0, 0, 0, .15);
      }

      .reviews .box-container .box img {
         height: 12rem;
         width: 12rem;
         border-radius: 50%;
         object-fit: cover;
         margin-bottom: 1.5rem;
         border: .7rem solid var(--light-bg);
         transition: all 0.3s ease;
      }

      .reviews .box-container .box:hover img {
         border-color: var(--primary);
         transform: scale(1.1);
      }

      .reviews .box-container .box p {
         padding: 1rem 0;
         font-size: 1.6rem;
         color: var(--light-color);
         line-height: 1.8;
         font-style: italic;
      }

      .reviews .box-container .box .stars {
         padding-top: 1rem;
      }

      .reviews .box-container .box .stars i {
         font-size: 1.8rem;
         color: var(--primary);
         margin: 0 .2rem;
      }

      .reviews .box-container .box h3 {
         padding: 1.2rem 0;
         font-size: 2.2rem;
         color: var(--black);
         text-transform: capitalize;
      }

      .authors .box-container .box {
         position: relative;
         overflow: hidden;
         border-radius: 1rem;
         box-shadow: var(--box-shadow);
         background: var(--white);
         transform: translateY(50px);
         opacity: 0;
      }

      .authors .box-container .box img {
         width: 100%;
         height: 28rem;
         object-fit: cover;
         transition: transform 0.5s ease;
      }

      .authors .box-container .box:hover img {
         transform: scale(1.1);
      }

      .authors .box-container .box .share {
         position: absolute;
         top: 1rem; right: -10rem;
         display: flex;
         flex-direction: column;
         gap: 0.7rem;
         transition: right 0.5s ease;
      }

      .authors .box-container .box:hover .share {
         right: 1rem;
      }

      .authors .box-container .box .share a {
         display: flex;
         align-items: center;
         justify-content: center;
         width: 4.5rem;
         height: 4.5rem;
         border-radius: 50%;
         color: var(--black);
         background: var(--white);
         font-size: 1.8rem;
         box-shadow: var(--box-shadow);
         transition: all 0.3s ease;
      }

      .authors .box-container .box .share a:hover {
         color: var(--white);
         background: var(--primary);
         transform: translateX(-5px);
      }

      .authors .box-container .box h3 {
         font-size: 2.2rem;
         color: var(--black);
         padding: 1.8rem 1.5rem;
         text-align: center;
         text-transform: capitalize;
         background: var(--white);
         position: relative;
         z-index: 1;
      }

      .authors .box-container .box h3::before {
         content: '';
         position: absolute;
         bottom: 0; left: 50%;
         transform: translateX(-50%);
         width: 5rem;
         height: .2rem;
         background: var(--primary);
         transition: width 0.3s ease;
         z-index: -1;
      }

      .authors .box-container .box:hover h3::before {
         width: 80%;
      }

      .cta-section {
         background: linear-gradient(rgba(25, 42, 86, 0.95), rgba(20, 172, 232, 0.95)), url('images/cta-bg.jpg') no-repeat;
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         padding: 6rem 2rem;
         text-align: center;
         position: relative;
         overflow: hidden;
      }

      .cta-section::before {
         content: '';
         position: absolute;
         top: 0; left: 0;
         width: 100%; height: 100%;
         background: url('images/pattern.png');
         opacity: 0.05;
      }

      .cta-content {
         position: relative;
         z-index: 1;
         opacity: 0;
         transform: translateY(50px);
      }

      .cta-section h2 {
         color: var(--white);
         font-size: 3.5rem;
         margin-bottom: 2rem;
         text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
      }

      .cta-section p {
         color: var(--white);
         font-size: 1.8rem;
         max-width: 70rem;
         margin: 0 auto 3rem;
         line-height: 1.8;
      }

      .cta-btn {
         background: var(--white);
         color: var(--secondary);
         font-weight: 600;
         padding: 1.5rem 4rem;
         font-size: 1.8rem;
      }

      .cta-btn:hover {
         background: var(--primary);
         color: var(--white);
      }

      @media (max-width: 991px) {
         .heading h3 {
            font-size: 3.5rem;
         }
         .title {
            font-size: 2.8rem;
         }
         .about .flex .content h3 {
            font-size: 2.4rem;
         }
         .cta-section h2 {
            font-size: 3rem;
         }
      }

      @media (max-width: 768px) {
         .heading h3 {
            font-size: 2.8rem;
         }
         .title {
            font-size: 2.5rem;
         }
         .about .flex .content h3 {
            font-size: 2.2rem;
         }
         .about .flex {
            padding: 3rem 2rem;
         }
         .cta-section h2 {
            font-size: 2.5rem;
         }
         .cta-section p {
            font-size: 1.6rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <div id="book-canvas"></div>
   <div class="heading-content">
      <h3>About BookCraft</h3>
      <p><a href="home.php">Home</a> / About</p>
   </div>
</div>

<section class="about">
   <div class="flex">
      <div class="image about-img">
         <img src="images/about-img.jpg" alt="About BookCraft">
      </div>
      <div class="content about-content">
         <h3>Why Choose BookCraft?</h3>
         <p>At BookCraft, we're passionate about connecting readers with their next favorite book. Our carefully curated collection spans across genres, offering something for every reading preference and interest.</p>
         <p>We believe in the power of literature to inspire, educate, and transform lives. Our team of dedicated book enthusiasts is always ready to help you discover your next literary adventure.</p>
         <a href="contact.php" class="btn">Contact Us</a>
      </div>
   </div>
</section>

<section class="reviews">
   <h1 class="title">Reader Testimonials</h1>
   <div class="box-container">
      <div class="box review-box">
         <img src="images/pic-1.png" alt="Customer Review">
         <p>"BookCraft has completely transformed my reading experience. Their recommendations are always spot-on, and their service is exceptional."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Sarah Johnson</h3>
      </div>

      <div class="box review-box">
         <img src="images/pic-2.png" alt="Customer Review">
         <p>"I've discovered so many amazing authors through BookCraft that I would have otherwise missed. Their selection is unmatched!"</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Michael Chen</h3>
      </div>

      <div class="box review-box">
         <img src="images/pic-3.png" alt="Customer Review">
         <p>"The personalized recommendations and fast delivery make BookCraft my go-to bookstore. I'm always excited to receive my orders!"</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Emily Rodriguez</h3>
      </div>

      <div class="box review-box">
         <img src="images/pic-4.png" alt="Customer Review">
         <p>"As an avid reader, I appreciate BookCraft's dedication to quality literature and their knowledgeable staff. Simply the best!"</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>David Thompson</h3>
      </div>

      <div class="box review-box">
         <img src="images/pic-5.png" alt="Customer Review">
         <p>"BookCraft has rekindled my love for reading. Their curated collections and special editions are absolutely worth every penny."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Olivia Parker</h3>
      </div>

      <div class="box review-box">
         <img src="images/pic-6.png" alt="Customer Review">
         <p>"The community events and book clubs organized by BookCraft have introduced me to fellow book lovers and enriched my reading experience."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>James Wilson</h3>
      </div>
   </div>
</section>

<section class="authors">
   <h1 class="title">Featured Authors</h1>
   <div class="box-container">
      <div class="box author-box">
         <img src="images/author-1.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Margaret Atwood</h3>
      </div>

      <div class="box author-box">
         <img src="images/author-2.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Haruki Murakami</h3>
      </div>

      <div class="box author-box">
         <img src="images/author-3.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Chimamanda Adichie</h3>
      </div>

      <div class="box author-box">
         <img src="images/author-4.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Neil Gaiman</h3>
      </div>

      <div class="box author-box">
         <img src="images/author-5.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Zadie Smith</h3>
      </div>

      <div class="box author-box">
         <img src="images/author-6.jpg" alt="Featured Author">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <h3>Salman Rushdie</h3>
      </div>
   </div>
</section>

<section class="cta-section">
   <div class="cta-content">
      <h2>Join Our Book Lovers Community</h2>
      <p>Subscribe to our newsletter for exclusive updates on new releases, author interviews, and special promotions. Be the first to know about our upcoming literary events and book club meetings.</p>
      <a href="register.php" class="btn cta-btn">Sign Up Now</a>
   </div>
</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
// Three.js Floating Books Animation
const createBookAnimation = () => {
   const canvas = document.getElementById('book-canvas');
   
   const scene = new THREE.Scene();
   const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
   
   const renderer = new THREE.WebGLRenderer({ alpha: true });
   renderer.setSize(window.innerWidth, window.innerHeight);
   canvas.appendChild(renderer.domElement);
   
   // Create floating books
   const books = [];
   const bookGeometry = new THREE.BoxGeometry(1, 1.5, 0.2);
   
   const bookColors = [
      0x27ae60, // green
      0x192a56, // blue
      0xf39c12, // orange
      0xe74c3c, // red
      0x9b59b6  // purple
   ];
   
   for (let i = 0; i < 20; i++) {
      const material = new THREE.MeshBasicMaterial({ color: bookColors[Math.floor(Math.random() * bookColors.length)] });
      const book = new THREE.Mesh(bookGeometry, material);
      
      // Random position
      book.position.x = Math.random() * 20 - 10;
      book.position.y = Math.random() * 20 - 10;
      book.position.z = Math.random() * 10 - 15;
      
      // Random rotation
      book.rotation.x = Math.random() * Math.PI;
      book.rotation.y = Math.random() * Math.PI;
      
      scene.add(book);
      books.push({
         mesh: book,
         rotationSpeed: {
            x: (Math.random() - 0.5) * 0.01,
            y: (Math.random() - 0.5) * 0.01
         },
         movementSpeed: {
            x: (Math.random() - 0.5) * 0.01,
            y: (Math.random() - 0.5) * 0.01
         }
      });
   }
   
   camera.position.z = 5;
   
   const animate = function () {
      requestAnimationFrame(animate);
      
      books.forEach(book => {
         book.mesh.rotation.x += book.rotationSpeed.x;
         book.mesh.rotation.y += book.rotationSpeed.y;
         
         book.mesh.position.x += book.movementSpeed.x;
         book.mesh.position.y += book.movementSpeed.y;
         
         // Boundary check and bounce
         if (Math.abs(book.mesh.position.x) > 12) {
            book.movementSpeed.x *= -1;
         }
         
         if (Math.abs(book.mesh.position.y) > 12) {
            book.movementSpeed.y *= -1;
         }
      });
      
      renderer.render(scene, camera);
   };
   
   animate();
   
   // Handle window resize
   window.addEventListener('resize', () => {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(window.innerWidth, window.innerHeight);
   });
};

// Initialize Three.js
createBookAnimation();

// GSAP Animations
document.addEventListener('DOMContentLoaded', () => {
   // Header animation
   gsap.to('.heading-content', {
      opacity: 1,
      duration: 1,
      y: 0,
      ease: 'power3.out',
      delay: 0.5
   });
   
   // About section animations
   gsap.to('.about-img', {
      scrollTrigger: {
         trigger: '.about',
         start: 'top 80%'
      },
      opacity: 1,
      x: 0,
      duration: 1,
      ease: 'power2.out'
   });
   
   gsap.to('.about-content', {
      scrollTrigger: {
         trigger: '.about',
         start: 'top 80%'
      },
      opacity: 1,
      x: 0,
      duration: 1,
      ease: 'power2.out',
      delay: 0.3
   });
   
   // Staggered review boxes animation
   gsap.to('.review-box', {
      scrollTrigger: {
         trigger: '.reviews',
         start: 'top 70%'
      },
      opacity: 1,
      y: 0,
      duration: 0.8,
      stagger: 0.15,
      ease: 'back.out(1.5)'
   });
   
   // Staggered author boxes animation
   gsap.to('.author-box', {
      scrollTrigger: {
         trigger: '.authors',
         start: 'top 70%'
      },
      opacity: 1,
      y: 0,
      duration: 0.8,
      stagger: 0.15,
      ease: 'back.out(1.5)'
   });
   
   // CTA section animation
   gsap.to('.cta-content', {
      scrollTrigger: {
         trigger: '.cta-section',
         start: 'top 70%'
      },
      opacity: 1,
      y: 0,
      duration: 1,
      ease: 'power2.out'
   });
   
   // Star ratings animation
   gsap.to('.stars i', {
      scrollTrigger: {
         trigger: '.reviews',
         start: 'top 70%'
      },
      scale: 1.2,
      stagger: 0.05,
      duration: 0.3,
      repeat: 1,
      yoyo: true,
      ease: 'power1.inOut'
   });
});
</script>

</body>
</html>