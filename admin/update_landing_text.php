<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_text') {
    try {
        // Get the text values from POST
        $welcomeText = $_POST['welcomeText'];
        $headingText = $_POST['headingText'];
        $subheadingText = $_POST['subheadingText'];

        // Update the text file
        $content = "<?php\n";
        $content .= "\$welcomeText = " . var_export($welcomeText, true) . ";\n";
        $content .= "\$headingText = " . var_export($headingText, true) . ";\n";
        $content .= "\$subheadingText = " . var_export($subheadingText, true) . ";\n";
        $content .= "?>";

        // Write to landing_text.php
        if (file_put_contents('landing_text.php', $content)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to write to file');
        }
    } catch (Exception $e) {
        error_log("Error in update_landing_text.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 