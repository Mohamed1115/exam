<?php
session_start();
include 'inc/header.php';
include_once 'db.php'; // Include database connection

class Product {
    private $conn; // Database connection
    public $id;
    public $name;
    public $price;
    public $description;
    public $image_path; // Define image_path property
    public $file_name;
    public $file_size;
    public $file_type;
    public $file_tmp;

    // Constructor to initialize the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to fetch product details by ID
    public function fetchProduct($id) {
        $sql = "SELECT name, price, description, image_path FROM products WHERE id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $this->id = $id;
                $this->name = $product['name'];
                $this->price = $product['price'];
                $this->description = $product['description'];
                $this->image_path = $product['image_path']; // Ensure this is set
                return true; // Product fetched successfully
            }
        }
        return false; // Product not found
    }

    // Method to update product details
    // Method to update product details
public function updateProduct($name, $price, $description, $image = null) {
    // If a new image is provided, handle image upload
    if ($image && !empty($image['name'])) { // Check if an image was uploaded
        $this->file_name = $image['name'];
        $this->file_size = $image['size'];
        $this->file_type = $image['type'];
        $this->file_tmp = $image['tmp_name'];

        // Handle image validation
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($this->file_type, $allowed_types)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed.");
        }

        // Validate file size (limit to 2MB)
        if ($this->file_size > 2 * 1024 * 1024) {
            throw new Exception("File size should not exceed 2MB.");
        }

        // Create unique file name
        $unique_file_name = uniqid() . "_" . basename($this->file_name);

        // Handle file upload
        if ($this->file_tmp && is_uploaded_file($this->file_tmp)) {
            if (!is_dir('images/')) {
                mkdir('images/', 0777, true); // Create the directory if it doesn't exist
            }
            if (move_uploaded_file($this->file_tmp, "images/" . $unique_file_name)) {
                // Update the database with new details
                return $this->updateDatabase($name, $price, $description, $unique_file_name);
            } else {
                throw new Exception("Failed to upload file.");
            }
        }
    }

    // If no new image uploaded, update details without changing the image
    return $this->updateDatabase($name, $price, $description, $this->image_path);
}


    // Method to update database with product details
    private function updateDatabase($name, $price, $description, $image_path) {
        $sql = "UPDATE products SET name = ?, price = ?, description = ?, image_path = ? WHERE id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("sdssi", $name, $price, $description, $image_path, $this->id);
            return $stmt->execute(); // Return true if update was successful
        }
        return false; // Update failed
    }

    // Method to create a new product (for completeness)
    public function createProduct($name, $price, $description, $image) {
        // Similar logic to handle image upload and database insertion can be implemented here.
    }

    // Method to delete a product (for completeness)
    public function deleteProduct($id) {
        // Implement deletion logic from the database
    }
}




// Create a new instance of the Product class
$product = new Product($conn);

// Check if product ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch product details from the database
    if (!$product->fetchProduct($product_id)) {
        $_SESSION['error'] = "Product not found.";
        header("Location: show.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: show.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $img = $_FILES['img'];

    // Update the product details
    if ($product->updateProduct($name, $price, $desc, $img)) {
        $_SESSION['succ'] = "Product updated successfully.";
        header("Location: show.php?id=" . $product->id);
        exit();
    } else {
        $_SESSION['err'] = "Failed to update product. Please check the image format and size.";
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product->price); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlTextarea1" class="form-label">Description:</label>
                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="desc" required><?php echo htmlspecialchars($product->description); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">Image:</label>
                    <input class="form-control" type="file" id="formFile" name="img">
                </div>

                <div class="col-lg-3">
                    <img src="<?php echo htmlspecialchars($product->image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product->name); ?>">
                </div>

                <center><button type="submit" class="btn btn-primary" name="submit">Update</button></center>
            </form>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
