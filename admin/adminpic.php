<?php
// Include database connection
include_once("../connection/connection.php");
$conn = connection();

// Define $targetDirectory outside the if block
$targetDirectory = "../user/contents/";

// Check if the form was submitted
if(isset($_POST["submit"])) {
    $targetFile = $targetDirectory . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 1) {
        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            // Image upload successful, insert data into database
            $filename = basename($_FILES["image"]["name"]); // Get filename
            $imageData = file_get_contents($targetFile); // Get image data
            $content = $_POST['content']; // Get content from form input

            // Insert data into database
            $sql = "INSERT INTO content_upload (filename, image_data, content) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $null = NULL; // Define NULL variable to bind to LONGBLOB
                $stmt->bind_param("bss", $null, $filename, $content); // Bind NULL for LONGBLOB, filename, and content
                $stmt->send_long_data(0, $imageData); // Send image data separately for LONGBLOB
                if ($stmt->execute()) {
                    // Redirect to the admin page after successful upload
                    header("Location: ../admin/adminpic.php");
                    exit();
                } else {
                    echo "Error executing SQL query: " . $stmt->error;
                }
            } else {
                echo "Error preparing SQL statement: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Retrieve announcements data from the database
$query = "SELECT filename, content FROM content_upload";
$result = $conn->query($query);

$announcements = ''; // Initialize $announcements variable

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $filename = $row['filename'];
        $content = $row['content'];
        $imageSrc = $targetDirectory . $filename; // Construct image source
        // Append each announcement to $announcements
        $announcements .= "<div class='announcement'>
                            <div class='image'>
                                <img src='$imageSrc' alt='Image'>
                            </div>
                            <div class='content'>
                                $content
                            </div>
                        </div>";
    }
} else {
    echo "Error retrieving data from the database: " . $conn->error;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
</head>
<body>
    <h2>Upload Image</h2>
    <form action="../admin/adminpic.php" method="post" enctype="multipart/form-data">
        <label for="image">Select Image:</label>
        <input type="file" name="image" id="image">
        <label for="content">Type Content:</label>
        <textarea name="content" id="content" rows="4" cols="50"></textarea>
        <input type="submit" value="Upload" name="submit">
    </form>

    <!-- Include your announcements container here -->
    <div class="announcements-container">
        <?php echo $announcements; ?>
    </div>

    <!-- Include your JavaScript code here -->
    <script>
        // JavaScript code
    </script>
</body>
</html>
