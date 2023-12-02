<?php


require_once('../../config.php');

$file = required_param('file', PARAM_TEXT);


// Check if the 'file' parameter is present in the GET request
if ($file) {
    // Sanitize the file path to prevent directory traversal
    $filePath = realpath($CFG->dataroot . '/' . $file);

    // Check if the file exists and is within the expected directory
    if ($filePath !== false && strpos($filePath, $CFG->dataroot) === 0 && file_exists($filePath)) {
        // Set appropriate headers for image display
        header('Content-Type: image/*');
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');

        // Output the image content
        readfile($filePath);
        exit;
    }
}

// If 'file' parameter is missing or the file doesn't exist, return a 404 response
http_response_code(404);
echo 'File not found';
exit;

?>
