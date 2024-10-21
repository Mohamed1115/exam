<?php
session_start(); // Start the session

// Include your header, table, etc.
include 'inc/header.php';
include_once 'db.php'; // Include the database connection

// Display success message if available
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Clear the message after displaying it
}

// Fetch products from the database
$sql = "SELECT id, name, price, description, image_path FROM products";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <div class="row">

        <?php
        if ($result->num_rows > 0) {
            // Output data of each row as cards
            while($row = $result->fetch_assoc()) {
        ?>
                <div class="col-lg-4 mb-3">
                    <div class="card">
                        <img src="images/<?php echo $row['image_path']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['name']; ?></h5>
                            <p class="text-muted"><?php echo $row['price']; ?> EGP</p>
                            <p class="card-text"><?php echo $row['description']; ?></p>
                            <a href="show.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Show</a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-info">Edit</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }

        $conn->close(); // Close the database connection
        ?>
        
    </div>
</div>

<?php include 'inc/footer.php'; ?>
