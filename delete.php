<?php
session_start();
include_once 'db.php';

class Product {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to delete a product by ID
    public function deleteProduct($id) {
        // SQL to delete a record
        $sql = "DELETE FROM products WHERE id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Product deleted successfully.";
            } else {
                $_SESSION['error'] = "Error deleting product.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Failed to prepare the SQL statement.";
        }
    }
}

// Check if an ID was provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $product = new Product($conn); // Create a Product object
    $product->deleteProduct($id); // Call the delete method
}

$conn->close();
header("Location: index.php");
exit();
?>
