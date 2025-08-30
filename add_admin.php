<?php
include 'config.php';

echo "Adding default admin user...\n";

$query = "INSERT INTO users (name, email, password, user_type) VALUES ('Admin', 'admin@bookcraft.com', '0192023a7bbd73250516f069df18b500', 'admin')";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Default admin user added successfully!\n";
    echo "You can now login with:\n";
    echo "Email: admin@bookcraft.com\n";
    echo "Password: admin123\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
