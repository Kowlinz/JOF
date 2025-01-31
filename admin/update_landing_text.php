<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Not authorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_text') {
    $type = $_POST['type'];
    $text = $_POST['text'];
    
    // Validate the type
    if (!in_array($type, ['welcome', 'heading', 'subheading'])) {
        die(json_encode(['success' => false, 'message' => 'Invalid text type']));
    }
    
    // Read the current file content
    $filePath = 'landing_text.php';
    $content = file_get_contents($filePath);
    
    // Update the appropriate variable
    switch ($type) {
        case 'welcome':
            $content = preg_replace('/\$welcomeText = ".*?";/', '$welcomeText = "' . addslashes($text) . '";', $content);
            break;
        case 'heading':
            $content = preg_replace('/\$headingText = ".*?";/', '$headingText = "' . addslashes($text) . '";', $content);
            break;
        case 'subheading':
            $content = preg_replace('/\$subheadingText = ".*?";/', '$subheadingText = "' . addslashes($text) . '";', $content);
            break;
    }
    
    // Write the updated content back to the file
    if (file_put_contents($filePath, $content) !== false) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update text']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 