<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

function connectDB() {
    $conn = new mysqli('localhost', 'root', '', 'jof_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = connectDB();
        
        $barberName = $_POST['barberName'];
        $barberDesc = $_POST['barberDesc'];
        
        if (!isset($_FILES['barberPic']) || $_FILES['barberPic']['size'] == 0) {
            throw new Exception('No image file was uploaded.');
        }
        
        // Check file size (limit to 16MB)
        if ($_FILES['barberPic']['size'] > 16777216) {
            throw new Exception('Image file is too large. Maximum size is 16MB.');
        }
        
        // Get image data
        $imageData = file_get_contents($_FILES['barberPic']['tmp_name']);
        
        // Verify it's an image
        if (!getimagesizefromstring($imageData)) {
            throw new Exception('Invalid image file.');
        }
        
        // Insert into database
        $query = "INSERT INTO barberpic_tbl (barberName, barbDesc, barberPic) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $barberName, $barberDesc, $imageData);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        error_log("Error in add_carousel.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 