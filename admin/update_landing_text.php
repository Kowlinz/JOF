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
    
    if (!file_exists($filePath)) {
        // Create the file if it doesn't exist
        $initialContent = "<?php\n" .
            "\$welcomeText = \"Welcome to JOF Barbershop\";\n" .
            "\$headingText = \"Your Style, Our Passion\";\n" .
            "\$subheadingText = \"Professional Haircuts and Grooming Services\";\n";
        file_put_contents($filePath, $initialContent);
    }
    
    // Read the current content
    $content = file_get_contents($filePath);
    
    if ($content === false) {
        die(json_encode(['success' => false, 'message' => 'Could not read configuration file']));
    }
    
    // Escape special characters in the text
    $escapedText = str_replace('"', '\"', $text);
    
    // Update the appropriate variable using a more reliable pattern
    switch ($type) {
        case 'welcome':
            $pattern = '/\$welcomeText\s*=\s*"[^"]*"/';
            $replacement = '$welcomeText = "' . $escapedText . '"';
            break;
        case 'heading':
            $pattern = '/\$headingText\s*=\s*"[^"]*"/';
            $replacement = '$headingText = "' . $escapedText . '"';
            break;
        case 'subheading':
            $pattern = '/\$subheadingText\s*=\s*"[^"]*"/';
            $replacement = '$subheadingText = "' . $escapedText . '"';
            break;
    }
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($newContent === null) {
        die(json_encode(['success' => false, 'message' => 'Error processing text replacement']));
    }
    
    // Write the updated content back to the file
    if (file_put_contents($filePath, $newContent) !== false) {
        // Clear the PHP opcode cache to ensure the changes are reflected immediately
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($filePath, true);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update text']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 