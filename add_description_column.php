<?php
include 'config.php';

echo "Adding description column to products table...\n";

// Check if description column already exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'description'");

if(mysqli_num_rows($check_column) == 0) {
    // Add description column
    $add_column_query = "ALTER TABLE `products` ADD COLUMN `description` TEXT AFTER `price`";
    $result = mysqli_query($conn, $add_column_query);
    
    if($result) {
        echo "Description column added successfully!\n";
        
        // Update existing products with default descriptions
        $update_query = "UPDATE `products` SET `description` = 'A wonderful book that offers great reading experience.' WHERE `description` IS NULL OR `description` = ''";
        $update_result = mysqli_query($conn, $update_query);
        
        if($update_result) {
            echo "Default descriptions added to existing products!\n";
        } else {
            echo "Error adding default descriptions: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Error adding description column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Description column already exists!\n";
}

echo "Database update completed!\n";
?>
