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
        
        if (!isset($_POST['barberpicID'])) {
            throw new Exception('Missing barberpicID parameter');
        }

        $barberpicID = $_POST['barberpicID'];
        $barberName = $_POST['barberName'];
        $barberDesc = $_POST['barberDesc'];
        
        // Start with the basic update query
        $query = "UPDATE barberpic_tbl SET 
                  barberName = ?, 
                  barbDesc = ?";
        $params = [$barberName, $barberDesc];
        $types = "ss";
        
        // Handle image upload if a new image was provided
        if (isset($_FILES['barberPic']) && $_FILES['barberPic']['size'] > 0) {
            // Check file size (limit to 16MB)
            if ($_FILES['barberPic']['size'] > 16777216) {
                throw new Exception('Image file is too large. Maximum size is 16MB.');
            }
            
            $imageData = file_get_contents($_FILES['barberPic']['tmp_name']);
            
            // Verify it's an image
            if (!getimagesizefromstring($imageData)) {
                throw new Exception('Invalid image file.');
            }
            
            // Add image data to update query
            $query .= ", barberPic = ?";
            $params[] = $imageData;
            $types .= "s";
        }
        
        // Complete the query
        $query .= " WHERE barberpicID = ?";
        $params[] = $barberpicID;
        $types .= "i";
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        error_log("Error in update_carousel.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 