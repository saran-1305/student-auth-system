<?php
// register.php
header('Content-Type: application/json');

// MySQL Connection settings
$db_host = 'localhost';
$db_user = 'root'; // default xampp/wamp user
$db_pass = '';
$db_name = 'student_auth_db';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get POST data
if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // small validation
    if(empty($name) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into MySQL using prepared statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful! You can now login."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
}
$conn->close();
?>
