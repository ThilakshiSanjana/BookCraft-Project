<?php
include 'config.php';

if(isset($_GET['id'])) {
   $product_id = $_GET['id'];
   $query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = ''") or die('query failed');
   
   if(mysqli_num_rows($query) > 0) {
      $product = mysqli_fetch_assoc($query);
      echo json_encode($product);
   } else {
      echo json_encode(["error" => "Product not found"]);
   }
} else {
   echo json_encode(["error" => "No ID provided"]);
}
?>