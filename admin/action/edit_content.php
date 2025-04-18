<?php 
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentId = $_POST['contentId'];
    $contentTitle = $_POST['contentTitle'];
    $lesson_number = $_POST['lessonNumber'];
    $term = $_POST['term'];
    $semester = $_POST['semester'];
    $contentDescription = $_POST['contentDescription'];

    $newVideo = null;

    // Handle file upload if a new video is provided
    if (isset($_FILES['contentVideo']) && $_FILES['contentVideo']['error'] === UPLOAD_ERR_OK) {
        $videoTmpPath = $_FILES['contentVideo']['tmp_name'];
        $videoName = $_FILES['contentVideo']['name'];
        $videoSize = $_FILES['contentVideo']['size'];

        // Validate file type and size (optional)
        $allowedExtensions = ['mp4', 'avi', 'mov', 'mkv'];
        $videoExtension = strtolower(pathinfo($videoName, PATHINFO_EXTENSION));
        if (!in_array($videoExtension, $allowedExtensions)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid video format. Allowed formats: MP4, AVI, MOV, MKV.'
            ]);
            exit;
        }

        if ($videoSize > 500 * 1024 * 1024) { // Limit: 500 MB
            echo json_encode([
                'status' => 'error',
                'message' => 'Video size exceeds 500 MB limit.'
            ]);
            exit;
        }

        // Move the uploaded video to the target directory
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid() . '_' . basename($videoName);
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($videoTmpPath, $destPath)) {
            $newVideo = $newFileName; // Save the new video file name for the database
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error uploading the file. Please try again.'
            ]);
            exit;
        }
    }

    // Prepare the SQL query to update the content
    if ($newVideo) {
        // Update with new video
        $stmt_update = $conn->prepare(
            "UPDATE content SET contents = ?, content_title = ?, term = ?, semester = ?, lesson_number = ?, video = ? WHERE id = ?"
        );
        $stmt_update->bind_param(
            "ssssssi",
            $contentDescription,
            $contentTitle,
            $term,
            $semester,
            $lesson_number,
            $newVideo,
            $contentId
        );
    } else {
        // Update without changing the video
        $stmt_update = $conn->prepare(
            "UPDATE content SET contents = ?, content_title = ?, term = ?, semester = ?, lesson_number = ? WHERE id = ?"
        );
        $stmt_update->bind_param(
            "sssssi",
            $contentDescription,
            $contentTitle,
            $term,
            $semester,
            $lesson_number,
            $contentId
        );
    }

    // Execute the query
    if ($stmt_update->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Content updated successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: Failed to update content.'
        ]);
    }

    $stmt_update->close();
}
?>
