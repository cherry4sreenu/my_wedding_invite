<?php
// Enable CORS and JSON response headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Create target directory to match the UI's expectations
$targetDir = "images/uploadedbyUsers/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// File to store gallery paths for the live feed
$galleryFile = "gallery.json";

if (isset($_FILES["files"])) {
    $uploadedFiles = [];
    $totalFiles = count($_FILES["files"]["name"]);

    // Loop through each uploaded file
    for ($i = 0; $i < $totalFiles; $i++) {
        $fileName = basename($_FILES["files"]["name"][$i]);
        
        // Sanitize file name
        $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);
        $targetFilePath = $targetDir . time() . "_" . $fileName;

        // Move file and add to our success array
        if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $targetFilePath)) {
            $uploadedFiles[] = $targetFilePath;
        }
    }

    if (count($uploadedFiles) > 0) {
        // --- Update the gallery.json file so the UI feed works ---
        $currentGallery = [];
        if (file_exists($galleryFile)) {
            $jsonContent = file_get_contents($galleryFile);
            $currentGallery = json_decode($jsonContent, true) ?: [];
        }
        
        // Add new images to the top of the feed
        $updatedGallery = array_merge($uploadedFiles, $currentGallery);
        file_put_contents($galleryFile, json_encode($updatedGallery));
        // ---------------------------------------------------------

        // Return the exact JSON structure the UI expects
        echo json_encode([
            "status" => "success", 
            "message" => "Files uploaded successfully",
            "files" => $uploadedFiles
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to move uploaded files."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No files uploaded."]);
}
?>