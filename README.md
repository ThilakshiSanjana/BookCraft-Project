# 📚 BookCraft - An E-commerce Bookstore

This project is a full-featured e-commerce website for an online bookstore named **BookCraft**. It's built with PHP and uses a MySQL database. The project includes both a user-facing storefront and an admin panel for managing the store.

### 🔑 Key Features:

**🧑‍💻 Customer-Facing:**
*   ✨ **User Authentication:** Secure user registration and login system.
*   🏠 **Homepage:** A dynamic homepage featuring a slider, featured books, and different categories.
*   📖 **Product Catalog:** Browse all books, view book details, and search for specific books.
*   🛒 **Shopping Cart:** Add/remove items from the cart and view the cart total.
*   💳 **Checkout:** A seamless checkout process to place orders.
*   📜 **Order History:** View past orders.
*   📧 **Contact Form:** A way for users to get in touch.
*   📱 **Responsive Design:** The user interface is designed to work on various screen sizes.
*   🤖 **AI Chatbot:** An interactive chatbot to help users with book recommendations.

**👑 Admin Panel:**
*   📊 **Dashboard:** An overview of total orders, products, users, and admin accounts.
*   📦 **Product Management:** Add, edit, and delete books from the store.
*   📈 **Order Management:** View and manage customer orders (e.g., update payment status).
*   👥 **User Management:** View and manage registered users and admin accounts.
*   💬 **Messages:** View messages sent through the contact form.

### 💻 Technologies Used:

*   **Backend:** PHP
*   **Database:** MySQL
*   **Frontend:** HTML, CSS, JavaScript
*   **Libraries:**
    *   **Swiper.js:** For the hero slider and testimonials.
    *   **AOS (Animate On Scroll):** For scroll animations.
    *   **Font Awesome:** For icons.
    *   **PHPMailer:** (Likely used for sending emails, based on the `vendor` directory).

### 🚀 Setup and Installation:

1.  **Prerequisites:** You'll need a local server environment like XAMPP or WAMP.
2.  **Database Setup:**
    *   Create a new database named `shop_db` in phpMyAdmin.
    *   Import the `shop_db.sql` file into the newly created database.
3.  **Project Files:**
    *   Place the project files in your server's web directory (e.g., `htdocs` for XAMPP).
4.  **Configuration:**
    *   The database connection is configured in `config.php`. The default settings are:
        ```php
        $conn = mysqli_connect('localhost','root','','shop_db');
        ```
        You can change these values if your setup is different.
5.  **Running the Application:**
    *   Start your Apache and MySQL servers.
    *   Open your web browser and navigate to `http://localhost/BookCraft` (or the name of your project folder).
