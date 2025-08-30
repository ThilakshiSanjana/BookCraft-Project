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

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }
}

// Since the products table doesn't have a category column, we'll create static categories
// You can add a category column to the products table if needed in the future
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>BookCraft - Your Premium Book Store</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
   <!-- Swiper CSS for sliders -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <!-- AOS CSS for animations -->
   <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
   
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
   
   <!-- Chatbot CSS -->
   <link rel="stylesheet" href="css/chatbot.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero Section with Slider -->
<section class="hero-slider">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide hero-slide hero-slide-1">
                <div class="hero-content" data-aos="fade-right">
                    <h1>Discover Your Next Favorite Book</h1>
                    <p>Hand-picked books delivered straight to your door with our curated collection.</p>
                    <div class="hero-buttons">
                        <a href="shop.php" class="primary-btn">Shop Now</a>
                        <a href="about.php" class="secondary-btn">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide hero-slide-2">
                <div class="hero-content" data-aos="fade-right">
                    <h1>Special Collection</h1>
                    <p>Explore our bestsellers and award-winning titles that readers love.</p>
                    <div class="hero-buttons">
                        <a href="shop.php?category=bestsellers" class="primary-btn">Bestsellers</a>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide hero-slide-3">
                <div class="hero-content" data-aos="fade-right">
                    <h1>New Arrivals</h1>
                    <p>Be the first to read the latest releases from your favorite authors.</p>
                    <div class="hero-buttons">
                        <a href="shop.php?sort=newest" class="primary-btn">View New Arrivals</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="features-container">
        <div class="feature" data-aos="fade-up" data-aos-delay="100">
            <i class="fas fa-truck"></i>
            <h3>Free Shipping</h3>
            <p>On orders over $50</p>
        </div>
        <div class="feature" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-undo"></i>
            <h3>Easy Returns</h3>
            <p>30-day return policy</p>
        </div>
        <div class="feature" data-aos="fade-up" data-aos-delay="300">
            <i class="fas fa-lock"></i>
            <h3>Secure Payment</h3>
            <p>Protected checkout</p>
        </div>
        <div class="feature" data-aos="fade-up" data-aos-delay="400">
            <i class="fas fa-headset"></i>
            <h3>24/7 Support</h3>
            <p>Customer care available</p>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories">
    <h2 class="section-title" data-aos="fade-up">Browse Categories</h2>
    <div class="categories-container">
        <?php  
        // Static categories since the database doesn't have category column
        $static_categories = ['Fiction', 'Non-Fiction', 'Educational', 'Children', 'Self-Help'];
        foreach($static_categories as $category) {
        ?>
        <a href="shop.php?category=<?php echo urlencode($category); ?>" class="category-card" data-aos="fade-up">
            <div class="category-icon">
                <i class="fas fa-book"></i>
            </div>
            <h3><?php echo $category; ?></h3>
        </a>
        <?php
        }
        ?>
        <a href="shop.php" class="category-card" data-aos="fade-up">
            <div class="category-icon">
                <i class="fas fa-th-large"></i>
            </div>
            <h3>All Books</h3>
        </a>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products">
    <h2 class="section-title" data-aos="fade-up">Featured Books</h2>
    <div class="products-filter" data-aos="fade-up">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="bestseller">Bestsellers</button>
        <button class="filter-btn" data-filter="new">New Arrivals</button>
        <button class="filter-btn" data-filter="sale">On Sale</button>
    </div>

    <div class="products-container">
        <?php  
        $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 8") or die('query failed');
        if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
                // Assign random tags for demo purposes
                $tags = ['bestseller', 'new', 'sale'];
                $random_tag = $tags[array_rand($tags)];
        ?>
        <div class="product-card" data-aos="fade-up" data-category="<?php echo $random_tag; ?>">
            <?php if($random_tag == 'sale'): ?>
            <div class="product-badge sale">Sale</div>
            <?php elseif($random_tag == 'new'): ?>
            <div class="product-badge new">New</div>
            <?php elseif($random_tag == 'bestseller'): ?>
            <div class="product-badge bestseller">Bestseller</div>
            <?php endif; ?>
            
            <div class="product-image">
                <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo $fetch_products['name']; ?>">
                <div class="product-actions">
                    <form action="" method="post">
                        <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                        <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                        <input type="hidden" name="product_quantity" value="1">
                        <button type="submit" name="add_to_cart" class="action-btn"><i class="fas fa-shopping-cart"></i></button>
                    </form>
                    <a href="book_details.php?id=<?php echo $fetch_products['id']; ?>" class="action-btn"><i class="fas fa-eye"></i></a>
                    <button class="action-btn wishlist-btn"><i class="far fa-heart"></i></button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title"><?php echo $fetch_products['name']; ?></h3>
                <?php if(isset($fetch_products['description']) && !empty($fetch_products['description'])): ?>
                <div class="product-description"><?php echo substr($fetch_products['description'], 0, 100) . '...'; ?></div>
                <?php endif; ?>
                <div class="product-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span>(4.5)</span>
                </div>
                <div class="product-price">
                    <?php if($random_tag == 'sale'): ?>
                    <span class="old-price">Rs<?php echo number_format($fetch_products['price'] * 1.2, 2); ?></span>
                    <?php endif; ?>
                    <span class="current-price">Rs<?php echo $fetch_products['price']; ?></span>
                </div>
                <form action="" method="post" class="quick-add">
                    <div class="quantity-control">
                        <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                        <input type="number" min="1" name="product_quantity" value="1" class="qty">
                        <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                    </div>
                    <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                    <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                    <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                </form>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No products added yet!</p>';
        }
        ?>
    </div>

    <div class="load-more" data-aos="fade-up">
        <a href="shop.php" class="view-all-btn">View All Books</a>
    </div>
</section>

<!-- Trending Section -->
<section class="trending">
    <div class="trending-container">
        <div class="trending-content" data-aos="fade-right">
            <h2>Trending This Month</h2>
            <p>Discover the books everyone is talking about this month.</p>
            <a href="shop.php?category=trending" class="primary-btn">Shop Trending</a>
        </div>
        <div class="trending-image" data-aos="fade-left">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvZOxS_jIXfRsgkJCRM80IzwcKUDyXX0XUHg&s" alt="Trending Books">
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <h2 class="section-title" data-aos="fade-up">What Our Customers Say</h2>
    <div class="swiper testimonialSwiper" data-aos="fade-up">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="https://media.istockphoto.com/id/1310814041/photo/portrait-of-a-businesswoman-standing-in-a-a-modern-office.jpg?s=612x612&w=0&k=20&c=rLDYEGaGfbFq6mJPLc2FHjc6KBKyJETu38y4a3x11cM=" alt="Customer">
                    </div>
                    <div class="testimonial-content">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"BookCraft has been my go-to bookstore for the past year. Their selection is amazing and delivery is always on time!"</p>
                        <h4>Sarah Johnson</h4>
                        <p class="customer-type">Regular Customer</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="data:image/jpeg;base64,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" alt="Customer">
                    </div>
                    <div class="testimonial-content">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"The customer service at BookCraft is exceptional. They helped me find the perfect gift for my bookworm friend!"</p>
                        <h4>Michael Davis</h4>
                        <p class="customer-type">New Customer</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="data:image/jpeg;base64,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" alt="Customer">
                    </div>
                    <div class="testimonial-content">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"I love the special collections they curate. It's like having a personal librarian who knows exactly what you'll enjoy reading."</p>
                        <h4>Jennifer Wilson</h4>
                        <p class="customer-type">Book Club Member</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- About Section -->
<section class="about">
    <div class="about-container">
        <div class="about-image" data-aos="fade-right">
            <img src="images/about-img.jpg" alt="About BookCraft">
        </div>
        <div class="about-content" data-aos="fade-left">
            <h2 class="section-title">About BookCraft</h2>
            <p>At BookCraft, we're passionate about connecting readers with books that inspire, entertain, and transform. Our carefully curated collection features everything from timeless classics to contemporary bestsellers.</p>
            <p>With our team of dedicated book lovers, we strive to provide personalized recommendations and exceptional service to all our customers.</p>
            <div class="about-stats">
                <div class="stat">
                    <h3>10k+</h3>
                    <p>Books</p>
                </div>
                <div class="stat">
                    <h3>5k+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="stat">
                    <h3>15+</h3>
                    <p>Years of Experience</p>
                </div>
            </div>
            <a href="about.php" class="primary-btn">Learn More</a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter" data-aos="fade-up">
    <div class="newsletter-container">
        <div class="newsletter-content">
            <h2>Subscribe to Our Newsletter</h2>
            <p>Stay updated with our latest book releases and exclusive offers.</p>
        </div>
        <form action="" method="post" class="newsletter-form">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit" name="subscribe" class="primary-btn">Subscribe</button>
        </form>
    </div>
</section>

<!-- Contact Section -->
<section class="home-contact" data-aos="fade-up">
    <div class="contact-container">
        <div class="contact-content">
            <h2>Have Any Questions?</h2>
            <p>Our dedicated team is here to help you with any inquiries about our books, orders, or services.</p>
            <a href="contact.php" class="primary-btn">Contact Us</a>
        </div>
    </div>
</section>

<!-- Chatbot -->
<div class="chatbot-container">
    <div class="chatbot-toggle">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chatbot-box">
        <div class="chatbot-header">
            <h3>BookCraft Assistant</h3>
            <button class="chatbot-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="message bot-message">
                <p>Hello! I'm BookCraft's AI assistant. How can I help you today?</p>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="user-input" placeholder="Type your message...">
            <button id="send-btn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

<!-- Chatbot JS -->
<script src="js/chatbot.js"></script>

<!-- Initialize components -->
<script>
    // Initialize AOS Animation
    AOS.init({
        duration: 800,
        offset: 100,
        once: true
    });
    
    // Initialize Hero Slider
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
    
    // Initialize Testimonial Slider
    var testimonialSwiper = new Swiper(".testimonialSwiper", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".testimonialSwiper .swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });
    
    // Products Filter
    $('.filter-btn').on('click', function() {
        const filter = $(this).data('filter');
        
        // Update active button
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if(filter === 'all') {
            $('.product-card').show();
        } else {
            $('.product-card').hide();
            $(`.product-card[data-category="${filter}"]`).show();
        }
    });
    
    // Quantity Controls
    $('.qty-btn.plus').on('click', function() {
        let input = $(this).siblings('.qty');
        let value = parseInt(input.val());
        input.val(value + 1);
    });
    
    $('.qty-btn.minus').on('click', function() {
        let input = $(this).siblings('.qty');
        let value = parseInt(input.val());
        if(value > 1) {
            input.val(value - 1);
        }
    });
    
    // Wishlist toggle
    $('.wishlist-btn').on('click', function() {
        $(this).find('i').toggleClass('far fas');
    });
</script>

<style>
    
    /* Additional styles for recommendation chatbot */
.chatbot-cart-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    margin-top: 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}

.chatbot-cart-btn:hover {
    background-color: #3c58a7;
    transform: translateY(-2px);
}

.bot-message {
    display: flex;
    flex-direction: column;
}

.bot-message p {
    margin-bottom: 5px;
}
   :root {
    --primary-color: #4a69bd;
    --secondary-color: #eb2f06;
    --accent-color: #f6b93b;
    --text-color: #333;
    --light-text: #666;
    --lighter-text: #999;
    --light-bg: #f8f9fa;
    --border-color: #e5e5e5;
    --success-color: #20bf6b;
    --white: #fff;
    --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    outline: none;
    border: none;
    transition: var(--transition);
}

html {
    font-size: 62.5%;
    scroll-behavior: smooth;
    scroll-padding-top: 8rem;
    overflow-x: hidden;
}

body {
    background: var(--white);
    color: var(--text-color);
    font-size: 1.6rem;
    line-height: 1.6;
}

section {
    padding: 5rem 9%;
}

.section-title {
    font-size: 3rem;
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -1rem;
    left: 50%;
    transform: translateX(-50%);
    width: 8rem;
    height: 0.3rem;
    background-color: var(--primary-color);
}

.primary-btn, .secondary-btn {
    display: inline-block;
    padding: 1.2rem 3rem;
    font-size: 1.6rem;
    font-weight: 500;
    border-radius: 5rem;
    cursor: pointer;
    text-align: center;
}

.primary-btn {
    background-color: var(--primary-color);
    color: var(--white);
}

.primary-btn:hover {
    background-color: #3c58a7;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.secondary-btn {
    background-color: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.secondary-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.view-all-btn {
    display: inline-block;
    padding: 1.2rem 3rem;
    background-color: var(--accent-color);
    color: var(--white);
    border-radius: 5rem;
    font-weight: 500;
    text-align: center;
    box-shadow: var(--shadow);
}

.view-all-btn:hover {
    background-color: #e59f1a;
    transform: translateY(-3px);
}

/* Hero Slider */
.hero-slider {
    position: relative;
    height: 65vh;
    overflow: hidden;
    padding: 0;
}

.hero-slide {
    background-size: cover;
    background-position: center;
    height: 65vh;
    display: flex;
    align-items: center;
    padding: 0 9%;
}

.hero-slide-1 {
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://cdn.mos.cms.futurecdn.net/6c3s8rMup3oTAUKywn4UTT.jpg');
}

.hero-slide-2 {
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('data:image/jpeg;base64,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');
}

.hero-slide-3 {
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('data:image/jpeg;base64,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');
}

.hero-content {
    color: var(--white);
    max-width: 60rem;
}

.hero-content h1 {
    font-size: 4.5rem;
    margin-bottom: 2rem;
    line-height: 1.2;
}

.hero-content p {
    font-size: 1.8rem;
    margin-bottom: 3rem;
}

.hero-buttons {
    display: flex;
    gap: 2rem;
}

.swiper-button-next,
.swiper-button-prev {
    color: var(--white);
}

.swiper-pagination-bullet {
    background: var(--white);
}

.swiper-pagination-bullet-active {
    background: var(--primary-color);
}

/* Features Section */
.features {
    background-color: var(--light-bg);
    padding: 4rem 9%;
}

.features-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
    gap: 2rem;
}

.feature {
    background-color: var(--white);
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    box-shadow: var(--shadow);
}

.feature i {
    font-size: 3.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.feature h3 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.feature p {
    font-size: 1.4rem;
    color: var(--light-text);
}

/* Categories Section */
.categories {
    padding: 5rem 9%;
}

.categories-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
    gap: 2rem;
}

.category-card {
    background-color: var(--light-bg);
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    box-shadow: var(--shadow);
    color: var(--text-color);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.category-icon {
    background-color: var(--primary-color);
    width: 6rem;
    height: 6rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.category-icon i {
    font-size: 2.5rem;
    color: var(--white);
}

.category-card h3 {
    font-size: 1.8rem;
}

/* Products Section */
.products {
    padding: 5rem 9%;
    background-color: var(--white);
}

.products-filter {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 3rem;
    gap: 1rem;
}

.filter-btn {
    padding: 0.8rem 2rem;
    border-radius: 5rem;
    background-color: var(--light-bg);
    color: var(--text-color);
    font-size: 1.4rem;
    cursor: pointer;
}

.filter-btn.active, .filter-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.products-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
    gap: 3rem;
}

.product-card {
    background-color: var(--white);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow);
    position: relative;
}

.product-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1.5rem;
    border-radius: 5rem;
    font-size: 1.2rem;
    font-weight: 500;
    z-index: 2;
}

.product-badge.sale {
    background-color: var(--secondary-color);
    color: var(--white);
}

.product-badge.new {
    background-color: var(--success-color);
    color: var(--white);
}

.product-badge.bestseller {
    background-color: var(--accent-color);
    color: var(--white);
}

.product-image {
    position: relative;
    overflow: hidden;
    height: 30rem;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-actions {
    position: absolute;
    bottom: -5rem;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 1rem;
    transition: bottom 0.3s ease;
}

.product-card:hover .product-actions {
    bottom: 0;
}

.action-btn {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background-color: var(--white);
    color: var(--text-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.action-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.product-info {
    padding: 2rem;
}

.product-title {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    font-weight: 500;
    height: 5rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
}

.product-description {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.4;
    opacity: 0.8;
}

.product-rating {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.product-rating i {
    color: var(--accent-color);
    margin-right: 0.3rem;
}

.product-rating span {
    color: var(--light-text);
    margin-left: 0.5rem;
}

.product-price {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.old-price {
    text-decoration: line-through;
    color: var(--lighter-text);
    margin-right: 1rem;
    font-size: 1.5rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 600;
    color: var(--primary-color);
}

.quantity-control {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.qty {
    width: 5rem;
    height: 4rem;
    text-align: center;
    font-size: 1.6rem;
    border: 1px solid var(--border-color);
    border-radius: 0;
}

.qty-btn {
    width: 4rem;
    height: 4rem;
    background-color: var(--light-bg);
    color: var(--text-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.qty-btn.minus {
    border-radius: 2rem 0 0 2rem;
}

.qty-btn.plus {
    border-radius: 0 2rem 2rem 0;
}

.qty-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.add-to-cart-btn {
    width: 100%;
    padding: 1.2rem;
    background-color: var(--primary-color);
    color: var(--white);
    font-size: 1.6rem;
    border-radius: 5rem;
    cursor: pointer;
    text-align: center;
}

.add-to-cart-btn:hover {
    background-color: #3c58a7;
}

.load-more {
    text-align: center;
    margin-top: 5rem;
}

/* Trending Section */
.trending {
    background-color: var(--light-bg);
    padding: 5rem 9%;
}

.trending-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
    gap: 4rem;
    align-items: center;
}

.trending-content h2 {
    font-size: 3rem;
    margin-bottom: 2rem;
}

.trending-content p {
    font-size: 1.6rem;
    margin-bottom: 3rem;
    color: var(--light-text);
}

.trending-image img {
    width: 100%;
    border-radius: 1rem;
    box-shadow: var(--shadow);
}

/* Testimonials */
.testimonials {
    padding: 5rem 9%;
    background-color: var(--white);
}

.testimonial-card {
    background-color: var(--light-bg);
    border-radius: 1rem;
    padding: 3rem;
    box-shadow: var(--shadow);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.testimonial-avatar {
    width: 8rem;
    height: 8rem;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 2rem;
    border: 5px solid var(--white);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.testimonial-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.testimonial-content {
    text-align: center;
}

.testimonial-content .rating {
    color: var(--accent-color);
    font-size: 1.6rem;
    margin-bottom: 1.5rem;
}

.testimonial-content p {
    font-size: 1.6rem;
    color: var(--text-color);
    margin-bottom: 2rem;
    font-style: italic;
}

.testimonial-content h4 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.customer-type {
    font-size: 1.4rem;
    color: var(--light-text);
}

/* About Section */
.about {
    padding: 5rem 9%;
    background-color: var(--white);
}

.about-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
    gap: 4rem;
    align-items: center;
}

.about-image img {
    width: 100%;
    border-radius: 1rem;
    box-shadow: var(--shadow);
}

.about-content h2 {
    font-size: 3rem;
    margin-bottom: 2rem;
}

.about-content p {
    font-size: 1.6rem;
    margin-bottom: 2rem;
    color: var(--light-text);
}

.about-stats {
    display: flex;
    gap: 3rem;
    margin-bottom: 3rem;
}

.stat h3 {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.stat p {
    font-size: 1.4rem;
    margin-bottom: 0;
}

/* Newsletter */
.newsletter {
    background-color: var(--light-bg);
    padding: 5rem 9%;
}

.newsletter-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
    gap: 4rem;
    align-items: center;
}

.newsletter-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.newsletter-content p {
    font-size: 1.6rem;
    color: var(--light-text);
}

.newsletter-form {
    display: flex;
    gap: 1rem;
}

.newsletter-form input {
    flex: 1;
    padding: 1.2rem 2rem;
    border-radius: 5rem;
    background-color: var(--white);
    font-size: 1.6rem;
}

/* Contact Section */
.home-contact {
    background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://hcommons.org/app/uploads/sites/1001669/2022/10/yin-adapted-2-scaled.jpg');
    background-size: cover;
    background-position: center;
    padding: 8rem 9%;
}

.contact-container {
    max-width: 70rem;
    margin: 0 auto;
}

.contact-content {
    text-align: center;
    color: var(--white);
}

.contact-content h2 {
    font-size: 3.5rem;
    margin-bottom: 2rem;
}

.contact-content p {
    font-size: 1.8rem;
    margin-bottom: 3rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    html {
        font-size: 55%;
    }
    
    section {
        padding: 4rem 5%;
    }
}

@media (max-width: 991px) {
    .hero-content h1 {
        font-size: 3.5rem;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-form input {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 768px) {
    html {
        font-size: 50%;
    }
    
    .hero-slider {
        height: 50vh;
    }
    
    .hero-slide {
        height: 50vh;
    }
    
    .hero-content h1 {
        font-size: 3rem;
    }
    
    .products-filter {
        gap: 0.5rem;
    }
    
    .filter-btn {
        padding: 0.7rem 1.5rem;
        font-size: 1.3rem;
    }
    
    .about-stats {
        flex-direction: column;
        gap: 2rem;
    }
}

@media (max-width: 450px) {
    html {
        font-size: 45%;
    }
    
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .products-container {
        grid-template-columns: 1fr;
    }
    
    .trending-container, .about-container, .newsletter-container {
        grid-template-columns: 1fr;
    }
}

/* Chatbot Styles */
.chatbot-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.chatbot-toggle:hover {
    background-color: #3c58a7;
    transform: scale(1.05);
}

.chatbot-toggle i {
    color: white;
    font-size: 24px;
}

.chatbot-box {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 350px;
    height: 500px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chatbot-header {
    background-color: var(--primary-color);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-header h3 {
    color: white;
    margin: 0;
    font-size: 18px;
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.chatbot-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
}

.message {
    max-width: 80%;
    padding: 10px 15px;
    margin-bottom: 15px;
    border-radius: 15px;
    font-size: 14px;
}

.bot-message {
    background-color: #f0f2f5;
    color: #333;
    align-self: flex-start;
    margin-right: auto;
    border-bottom-left-radius: 5px;
}

.user-message {
    background-color: var(--primary-color);
    color: white;
    align-self: flex-end;
    margin-left: auto;
    border-bottom-right-radius: 5px;
}

.chatbot-input {
    display: flex;
    padding: 10px 15px;
    border-top: 1px solid #eee;
    background-color: #f9f9f9;
}

.chatbot-input input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
}

.chatbot-input button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    margin-left: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-input button:hover {
    background-color: #3c58a7;
}

.chatbot-typing {
    display: flex;
    align-items: center;
    margin-top: 5px;
    margin-bottom: 15px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    background-color: #999;
    border-radius: 50%;
    margin-right: 5px;
    animation: typingAnimation 1s infinite;
}

.typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typingAnimation {
    0% { opacity: 0.4; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
    100% { opacity: 0.4; transform: scale(1); }
}

/* Responsive design for chatbot */
@media (max-width: 768px) {
    .chatbot-box {
        width: 300px;
    }
}

@media (max-width: 450px) {
    .chatbot-box {
        width: 280px;
        right: -10px;
    }
    
    .chatbot-toggle {
        width: 50px;
        height: 50px;
    }
}
</style>

<script>
   // Add to your existing script.js file or create a new one

// Wishlist functionality
document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            showToast('Added to wishlist!');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            showToast('Removed from wishlist!');
        }
    });
});

// Quantity controls
document.querySelectorAll('.qty-btn.plus').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.qty');
        let value = parseInt(input.value);
        input.value = value + 1;
    });
});

document.querySelectorAll('.qty-btn.minus').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.qty');
        let value = parseInt(input.value);
        if (value > 1) {
            input.value = value - 1;
        }
    });
});

// Product filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        // Toggle active class
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');
        
        // Filter products
        document.querySelectorAll('.product-card').forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else {
                if (card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    });
});

// Show toast message
function showToast(message) {
    // Create toast element if it doesn't exist
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    
    // Set message and show toast
    toast.textContent = message;
    toast.classList.add('show');
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add toast styles to document
const style = document.createElement('style');
style.textContent = `
    .toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--primary-color);
        color: white;
        padding: 12px 24px;
        border-radius: 5px;
        font-size: 14px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 9999;
    }
    
    .toast.show {
        opacity: 1;
        visibility: visible;
    }
`;
document.head.appendChild(style);

// Chatbot functionality
/**
 * BookCraft Recommendation Chatbot
 * A rule-based chatbot for book recommendations
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const chatbotToggle = document.querySelector('.chatbot-toggle');
    const chatbotBox = document.querySelector('.chatbot-box');
    const chatbotClose = document.querySelector('.chatbot-close');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Book recommendation data
    const bookGenres = [
        'fiction', 'resale', 'stationary','fantasy', 'science fiction', 'sci-fi', 'mystery', 'thriller',
        'i m educator', 'historical fiction', 'i m student', 'i m reader', 'self-help',
        'business', 'adventure', 'young adult', 'children', 'horror', 'poetry',
        'comics', 'manga', 'classics', 'philosophy', 'psychology'
    ];
    
    const bookRecommendations = {
        'fiction': [
            { title: 'The Night Circus', author: 'Erin Morgenstern', price: 16.99 },
            { title: 'Normal People', author: 'Sally Rooney', price: 15.99 },
            { title: 'The Midnight Library', author: 'Matt Haig', price: 18.99 }
        ],
        'fantasy': [
            { title: 'The Name of the Wind', author: 'Patrick Rothfuss', price: 19.99 },
            { title: 'A Game of Thrones', author: 'George R.R. Martin', price: 22.99 },
            { title: 'The Fifth Season', author: 'N.K. Jemisin', price: 17.99 }
        ],
                'resale': [
            { title: 'The Name of the Wind', author: 'Patrick Rothfuss', price: 19.99 },
            { title: 'A Game of Thrones', author: 'George R.R. Martin', price: 22.99 },
            { title: 'The Fifth Season', author: 'N.K. Jemisin', price: 17.99 }
        ],
                'stationary': [
            { title: 'The Name of the Wind', author: 'Patrick Rothfuss', price: 19.99 },
            { title: 'A Game of Thrones', author: 'George R.R. Martin', price: 22.99 },
            { title: 'The Fifth Season', author: 'N.K. Jemisin', price: 17.99 }
        ],
        'science fiction': [
            { title: 'Dune', author: 'Frank Herbert', price: 24.99 },
            { title: 'Project Hail Mary', author: 'Andy Weir', price: 21.99 },
            { title: 'The Three-Body Problem', author: 'Liu Cixin', price: 19.99 }
        ],
        'sci-fi': [
            { title: 'Dune', author: 'Frank Herbert', price: 24.99 },
            { title: 'Project Hail Mary', author: 'Andy Weir', price: 21.99 },
            { title: 'The Three-Body Problem', author: 'Liu Cixin', price: 19.99 }
        ],
        'mystery': [
            { title: 'The Silent Patient', author: 'Alex Michaelides', price: 17.99 },
            { title: 'The Thursday Murder Club', author: 'Richard Osman', price: 16.99 },
            { title: 'Gone Girl', author: 'Gillian Flynn', price: 15.99 }
        ],
        'thriller': [
            { title: 'The Girl on the Train', author: 'i recomented education  book', price: 16.99 },
            { title: 'The Guest List', author: 'Lucy Foley', price: 18.99 },
            { title: 'i recomented education  book', author: 'Dan Brown', price: 14.99 }
        ],
        'i m educator': [
            { title: 'master guide', author: 'Sally Thorne', price: 15.99 },
            { title: 'Red, White & Royal Blue', author: 'Casey McQuiston', price: 16.99 },
            { title: 'i m recomendated education book', author: 'Colleen Hoover', price: 18.99 }
        ],
        'historical fiction': [
            { title: 'The Book Thief', author: 'Markus Zusak', price: 19.99 },
            { title: 'All the Light We Cannot See', author: 'Anthony Doerr', price: 22.99 },
            { title: 'The Nightingale', author: 'Kristin Hannah', price: 20.99 }
        ],
        'i m student': [
            { title: 'Sapiens', author: 'i recomented resale  book', price: 24.99 },
            { title: 'The Splendid and the Vile', author: 'Erik Larson', price: 21.99 },
            { title: 'i recomented resale  book', author: ' maths', price: 18.99 }
        ],
        'i m reader': [
            { title: 'Becoming', author: 'fiction', price: 26.99 },
            { title: 'The Code Breaker', author: 'Walter Isaacson', price: 25.99 },
            { title: 'i m recomendated fiction book', author: 'Tara Westover', price: 19.99 }
        ],
        'self-help': [
            { title: 'Atomic Habits', author: 'James Clear', price: 17.99 },
            { title: 'The Subtle Art of Not Giving a F*ck', author: 'Mark Manson', price: 16.99 },
            { title: 'Think Again', author: 'Adam Grant', price: 18.99 }
        ],
        'business': [
            { title: 'Think and Grow Rich', author: 'Napoleon Hill', price: 14.99 },
            { title: 'Start with Why', author: 'Simon Sinek', price: 18.99 },
            { title: 'Good to Great', author: 'Jim Collins', price: 22.99 }
        ]
    };
    
    // Conversation state
    let conversationState = {
        expectingGenre: false,
        recommendedBooks: [],
        lastRecommendationIndex: 0
    };
    
    // Toggle chatbot visibility
    chatbotToggle.addEventListener('click', function() {
        chatbotBox.style.display = 'flex';
        chatbotToggle.style.display = 'none';
    });
    
    // Close chatbot
    chatbotClose.addEventListener('click', function() {
        chatbotBox.style.display = 'none';
        chatbotToggle.style.display = 'flex';
    });
    
    // Send message on button click
    sendBtn.addEventListener('click', sendMessage);
    
    // Send message on Enter key
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    function sendMessage() {
        const message = userInput.value.trim();
        if (message === '') return;
        
        // Add user message to chat
        addMessage(message, 'user');
        
        // Clear input field
        userInput.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Process message and get response (with a slight delay to simulate thinking)
        setTimeout(() => {
            const botResponse = processUserMessage(message);
            removeTypingIndicator();
            
            // If response is an array, send multiple messages
            if (Array.isArray(botResponse)) {
                botResponse.forEach((response, index) => {
                    // Add delay between messages
                    setTimeout(() => {
                        addMessage(response, 'bot');
                    }, index * 600);
                });
            } else {
                addMessage(botResponse, 'bot');
            }
        }, 800);
    }
    
    function addMessage(message, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender + '-message');
        
        const messageText = document.createElement('p');
        
        // Check if this is a book recommendation with a button
        if (typeof message === 'object' && message.book) {
            messageText.innerHTML = `<strong>${message.book.title}</strong> by ${message.book.author} - $${message.book.price}`;
            
            const addToCartBtn = document.createElement('button');
            addToCartBtn.textContent = 'Add to Cart';
            addToCartBtn.classList.add('chatbot-cart-btn');
            addToCartBtn.onclick = function() {
                addToCart(message.book);
                addMessage(`I've added "${message.book.title}" to your cart!`, 'bot');
            };
            
            messageElement.appendChild(messageText);
            messageElement.appendChild(addToCartBtn);
        } else {
            messageText.innerHTML = message;
            messageElement.appendChild(messageText);
        }
        
        chatbotMessages.appendChild(messageElement);
        
        // Scroll to bottom
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    function showTypingIndicator() {
        const typingIndicator = document.createElement('div');
        typingIndicator.classList.add('chatbot-typing');
        typingIndicator.id = 'typing-indicator';
        
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('div');
            dot.classList.add('typing-dot');
            typingIndicator.appendChild(dot);
        }
        
        chatbotMessages.appendChild(typingIndicator);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    function processUserMessage(message) {
        const lowercaseMessage = message.toLowerCase();
        
        // If we're expecting a genre response
        if (conversationState.expectingGenre) {
            conversationState.expectingGenre = false;
            
            // Check if user mentioned a genre we recognize
            const mentionedGenre = bookGenres.find(genre => lowercaseMessage.includes(genre));
            
            if (mentionedGenre) {
                // Get recommendations for this genre
                const recommendations = bookRecommendations[mentionedGenre] || [];
                conversationState.recommendedBooks = recommendations;
                conversationState.lastRecommendationIndex = 0;
                
                if (recommendations.length > 0) {
                    const responses = [
                        `Great! I have some excellent ${mentionedGenre} books to recommend:`
                    ];
                    
                    // Add book recommendations (just the first one initially)
                    if (recommendations.length > 0) {
                        responses.push({ book: recommendations[0] });
                        responses.push("Would you like to see more recommendations or add this to your cart?");
                    }
                    
                    return responses;
                } else {
                    return `I'm sorry, I don't have specific recommendations for ${mentionedGenre} at the moment. Would you like to try another genre?`;
                }
            } else {
                return "I'm not familiar with that genre. Here are some genres I know about: fiction, fantasy, resale, stationery, thriller, i recomented education  book, i m recomended resale book, i m recomended fiction book, self-help. Which one interests you?";
            }
        }
        
        // Check for recommendation request
        if (lowercaseMessage.includes('recommend') || 
            lowercaseMessage.includes('suggestion') || 
            lowercaseMessage.includes('suggest a book') ||
            lowercaseMessage.includes('what should i read')) {
            
            conversationState.expectingGenre = true;
            return "I'd be happy to recommend some books! What genre are you interested in?";
        }
        
        // Check for "more" or "another" recommendation
        if ((lowercaseMessage.includes('more') || 
             lowercaseMessage.includes('another') ||
             lowercaseMessage.includes('next')) && 
            conversationState.recommendedBooks.length > 0) {
            
            conversationState.lastRecommendationIndex++;
            
            // Check if we have more recommendations
            if (conversationState.lastRecommendationIndex < conversationState.recommendedBooks.length) {
                const nextBook = conversationState.recommendedBooks[conversationState.lastRecommendationIndex];
                return [
                    { book: nextBook },
                    conversationState.lastRecommendationIndex === conversationState.recommendedBooks.length - 1 
                        ? "That's my last recommendation in this category. Would you like to explore another genre?" 
                        : "Would you like to see more recommendations or add this to your cart?"
                ];
            } else {
                return "That's all the recommendations I have for this category. Would you like to explore another genre?";
            }
        }
        
        // Check for genre directly
        const directGenre = bookGenres.find(genre => lowercaseMessage.includes(genre));
        if (directGenre) {
            const recommendations = bookRecommendations[directGenre] || [];
            conversationState.recommendedBooks = recommendations;
            conversationState.lastRecommendationIndex = 0;
            
            if (recommendations.length > 0) {
                const responses = [
                    `Here are some great ${directGenre} books I recommend:`
                ];
                
                // Add book recommendations (just the first one initially)
                if (recommendations.length > 0) {
                    responses.push({ book: recommendations[0] });
                    responses.push("Would you like to see more recommendations or add this to your cart?");
                }
                
                return responses;
            } else {
                return `I'm sorry, I don't have specific recommendations for ${directGenre} at the moment. Would you like to try another genre?`;
            }
        }
        
        // Check for add to cart intent
        if (lowercaseMessage.includes('add to cart') || 
            lowercaseMessage.includes('buy') || 
            lowercaseMessage.includes('purchase')) {
            
            if (conversationState.recommendedBooks.length > 0 && 
                conversationState.lastRecommendationIndex < conversationState.recommendedBooks.length) {
                
                const bookToAdd = conversationState.recommendedBooks[conversationState.lastRecommendationIndex];
                addToCart(bookToAdd);
                return `I've added "${bookToAdd.title}" to your cart! Would you like to see more recommendations?`;
            } else {
                return "I'm not sure which book you'd like to add to your cart. Could you specify a book title or ask for recommendations first?";
            }
        }
        
        // Check for greeting
        if (lowercaseMessage.includes('hello') || 
            lowercaseMessage.includes('hi') || 
            lowercaseMessage.includes('hey')) {
            return "Hello! I'm BookCraft's book recommendation assistant. I can help you find your next great read. What genre of books are you interested in?";

        }

        }
        
        // Check for student
        if (lowercaseMessage.includes('i m student')) {
            return "You're welcome! Happy reading! If you need more book recommendations in the future, just ask.";
        
             }
        
        // Check for student
        if (lowercaseMessage.includes('i m reader')) {
            return "You're welcome! Happy reading! If you need more book recommendations in the future, just ask.";
        // Check for thanks
        if (lowercaseMessage.includes('thank')) {
            return "You're welcome! Happy reading! If you need more book recommendations in the future, just ask.";
        }


        
        // Check for help
        if (lowercaseMessage.includes('help') || lowercaseMessage.includes('how do you work')) {
            return "I can help you find book recommendations based on genres you're interested in. Just tell me what kind of books you enjoy reading, or ask for a recommendation directly!";
        }
        
        // Default response
        return "I'm here to help you find your next favorite book. You can ask me for recommendations by genre, or just tell me what kinds of books you enjoy reading!";
    }
    
    function addToCart(book) {
        // Create a form to submit the book to the cart
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        form.style.display = 'none';
        
        // Create input fields for product info
        const productName = document.createElement('input');
        productName.name = 'product_name';
        productName.value = book.title;
        
        const productPrice = document.createElement('input');
        productPrice.name = 'product_price';
        productPrice.value = book.price;
        
        const productImage = document.createElement('input');
        productImage.name = 'product_image';
        productImage.value = 'default-book.jpg'; // Default image
        
        const productQuantity = document.createElement('input');
        productQuantity.name = 'product_quantity';
        productQuantity.value = 1;
        
        const addToCartBtn = document.createElement('input');
        addToCartBtn.type = 'submit';
        addToCartBtn.name = 'add_to_cart';
        
        // Append inputs to form
        form.appendChild(productName);
        form.appendChild(productPrice);
        form.appendChild(productImage);
        form.appendChild(productQuantity);
        form.appendChild(addToCartBtn);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }

    // Start with a welcome message
    setTimeout(() => {
        addMessage("Hello! I'm BookCraft's AI book recommendation assistant. I can help you find your next great read based on your interests. What genre of books do you enjoy?", 'bot');
    }, 1000);
});
</script>
</body>
</html>