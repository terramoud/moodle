<?php


require_once('../../../config.php');
require_login();

$outfile;
// Function to validate and handle file uploads
function handleFileUpload($licenseId, $files)
{
    global $CFG, $DB, $USER, $outfile;

    // Validate license ID (should be an integer)
    if (!is_numeric($licenseId)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid license ID']);
        exit;
    }

    // Validate and create the directory for the license ID
    $licenseDir = $CFG->dataroot . '/Licenses/' . $licenseId;
    if (!file_exists($licenseDir)) {
        if (!mkdir($licenseDir, 0755, true)) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to create directory for license']);
            exit;
        }
    }

    // Iterate through each file
    foreach ($files as $file) {
            // Validate file type and size
            $fileName = generateRandomFileName($files['file']['name']);
            $fileSize = $files['file']['size'][$key];
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'gif', 'png'];
            if (!in_array(strtolower($fileType), $allowedTypes)) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'Invalid file type']);
                exit;
            }

            // Check file size (100MB limit)
            $maxFileSize = 100 * 1024 * 1024;
            if ($fileSize > $maxFileSize) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'File size exceeds limit']);
                exit;
            }

            // Move the uploaded file to the destination directory
            $destination = $licenseDir . '/' . $fileName;
            if (!move_uploaded_file($files['file']['tmp_name'], $destination)) {
                http_response_code(500); // Internal Server Error
                echo json_encode(['error' => 'Failed to move uploaded file']);
                exit;
            }
    }

    $outfile = $destination;
}

// Function to generate a random filename
function generateRandomFileName($originalName)
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $randomName = md5(uniqid(rand(), true)) . '.' . $extension;
    return $randomName;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $licenseId = filter_input(INPUT_POST, 'license_id', FILTER_SANITIZE_NUMBER_INT);
    $files = $_FILES;

    // Check if both license ID and files are present
    if ($licenseId === null || !$files['file']['tmp_name']) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Missing license ID or files']);
        exit;
    }

    // Handle file upload
    handleFileUpload($licenseId, $files);

    $item = new stdClass;
    $item->id = $licenseId;
    $item->itemid = str_replace($CFG->dataroot . "/Licenses/" . $licenseId . "/", "", $outfile);
    $DB->update_record('mcdean_license_user', $item);
    // Respond with success
    http_response_code(200); // OK
    echo json_encode(['message' => 'Files uploaded successfully', 'itemid' => $item->itemid]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}
?>
