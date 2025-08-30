<?php
include 'config.php';

echo "Testing database connection...\n";

$result = mysqli_query($conn, "SELECT * FROM users");
if ($result) {
    echo "Database connected successfully. Users table exists.\n";
    echo "Users in database:\n";
    while($row = mysqli_fetch_assoc($result)) {
        echo "User: " . $row['name'] . " | Email: " . $row['email'] . " | Type: " . $row['user_type'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

// Test MD5 hash
echo "\nTesting MD5 hash for 'admin123': " . md5('admin123') . "\n";
echo "Testing MD5 hash for 'hello': " . md5('hello') . "\n";
?>
