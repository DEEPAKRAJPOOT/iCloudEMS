<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'root', 'Root@123456', 'iCloudEMS');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['upload'])) {
    // Save uploaded file to a designated folder
    $targetDir = "/var/www/html/iCloudEMS/uploads";
    $targetFile = $targetDir . basename($_FILES["file"]["name"]);

    // Ensure the file is moved to the MySQL secure folder
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        // Add the file to a job queue in the database
        $stmt = $conn->prepare("INSERT INTO csv_import_jobs (file_name, status) VALUES (?, 'pending')");
        $stmt->bind_param("s", $targetFile);
        $stmt->execute();
        $stmt->close();

        echo "File uploaded successfully. Import process will start in the background.";
    } else {
        echo "Error uploading file.";
    }
}

$conn->close();
?>
