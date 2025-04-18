<?php 
include('../../connection.php'); 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentTitle = $_POST['contentTitle'] ?? '';
    $contentDescription = $_POST['contentDescription'] ?? '';
    $lesson_number = $_POST['lesson_number'] ?? '';
    $term = $_POST['term'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $videoPathForDb = null; // Default to null since video is optional

    // Check if a file is uploaded
    if (isset($_FILES['contentVideo']) && $_FILES['contentVideo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['contentVideo']['tmp_name'];
        $fileName = $_FILES['contentVideo']['name'];
        $fileSize = $_FILES['contentVideo']['size'];
        $fileType = $_FILES['contentVideo']['type'];
        
        $allowedExtensions = ['mp4', 'avi', 'mov'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid file type. Only MP4, AVI, and MOV are allowed.'
            ]);
            exit;
        }

        $uploadDir = 'uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid() . '_' . $fileName;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $videoPathForDb = $newFileName; // Save the uploaded file path for the database
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error uploading the file. Please try again.'
            ]);
            exit;
        }
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO content (content_title, contents, video, term, semester, lesson_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $contentTitle, $contentDescription, $videoPathForDb, $term, $semester, $lesson_number);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Content added successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: Failed to insert content.'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>
