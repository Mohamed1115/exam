<?php
session_start();
include 'inc/header.php';
include_once 'db.php'; // Include database connection

// Check if the product ID is passed in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare SQL to get product details by ID
    $sql = "SELECT name, price, description, image_path FROM products WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id); // Bind the product ID

        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a product was found
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc(); // Fetch product details
        } else {
            // If no product found, redirect back with an error message
            $_SESSION['error'] = "Product not found.";
            header("Location: show.php");
            exit();
        }

        $stmt->close();
    }
} else {
    // If no ID provided, redirect back with an error message
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: show.php");
    exit();
}
if (isset($_SESSION['succ'])) {
    echo '<div class="alert alert-success">' . $_SESSION['succ'] . '</div>';
    unset($_SESSION['succ']); // Clear the message after displaying it
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-6">
            <img src="images/<?php echo $product['image_path']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="col-lg-6">
            <h5><?php echo $product['name']; ?></h5>
            <p class="text-muted">Price: <?php echo $product['price']; ?> EGP</p>
            <p><?php echo $product['description']; ?></p>
            <a href="index.php" class="btn btn-primary">Back</a>
            <a href="edit.php?id=<?php echo $product_id; ?>" class="btn btn-info">Edit</a>
            <a href="delete.php?id=<?php echo $product_id; ?>" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
