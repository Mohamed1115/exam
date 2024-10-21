<?php
session_start(); // Start the session
include_once 'db.php'; // Include the database connection

class Recive {
    public $name;
    public $price;
    public $desc;
    public $file;
    public $file_name;
    public $file_size;
    public $file_type;
    public $file_tmp;

    // Constructor to initialize values and handle file upload
    function __construct($name, $price, $desc, $file, $conn) {
        $this->name = $name;
        $this->price = $price;
        $this->desc = $desc;
        $this->file = $file;

        // Extracting file data from the $file array
        $this->file_name = $file['name'];
        $this->file_size = $file['size'];
        $this->file_type = $file['type'];
        $this->file_tmp = $file['tmp_name'];

        // Validate file type (only allow jpg, png, gif)
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
        if ($this->file && is_uploaded_file($this->file_tmp)) {
            if (!is_dir('images/')) {
                mkdir('images/', 0777, true); // Create the directory if it doesn't exist
            }
            if (move_uploaded_file($this->file_tmp, "images/" . $unique_file_name)) {
                // Insert data into the database after successful upload
                $this->insertData($conn, $unique_file_name);
            } else {
                throw new Exception("Failed to upload file.");
            }
        }
    }

    // Function to insert data into MySQL database
    public function insertData($conn, $unique_file_name) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $this->name, $this->price, $this->desc, $unique_file_name);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data inserted successfully."; // Success session message
            header("Location: index.php"); // Redirect to show.php
            exit(); // Stop further script execution
        } else {
            $_SESSION['error'] = "Error inserting data: " . $stmt->error; // Error session message
            header("Location: add.php"); // Redirect to add.php
            exit(); // Stop further script execution
        }

        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $n = $_POST['name'];
    $p = $_POST['price'];
    $desc = $_POST['desc'];
    $f = $_FILES['img'];

    try {
        $rv = new Recive($n, $p, $desc, $f, $conn);
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Store the error message in the session
        header("Location: add.php"); // Redirect back to add.php in case of an error
        exit(); // Stop further script execution
    }
}

$conn->close(); // Close the database connection
?>
