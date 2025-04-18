<?php 
include '../../connection.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $input['id'];

    $stmt_delete = $conn->prepare("UPDATE content SET deleted = 1 WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    if ($stmt_delete->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Content deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: Failed to delete content.'
        ]);
    }
    $stmt_delete->close();
}

?>