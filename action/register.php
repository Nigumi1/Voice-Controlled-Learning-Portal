<?php 
include '../connection.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $input['fname'];
    $mname = $input['mname'];
    $lname = $input['lname'];
    $email = $input['email'];
    $password = password_hash($input['password'], PASSWORD_DEFAULT);

    function checkEmailExists($conn, $email) {
        $stmt = $conn->prepare("SELECT email FROM user_info WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        error_log("Email check: $email exists: " . ($exists ? 'true' : 'false'));
        return $exists;
    }

    if (checkEmailExists($conn, $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        $stmt = $conn->prepare("INSERT INTO user_info (fname, mname, lname, email, pass) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fname, $mname, $lname, $email, $password);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: Failed to register']);
        }
        $stmt->close();
    }
}
?>