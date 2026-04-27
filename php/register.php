<?php
// register.php
header('Content-Type: application/json');

// Database connection using MYSQL_PUBLIC_URL
$db_url = getenv("MYSQL_PUBLIC_URL");
$url_parts = parse_url($db_url);

$host = !empty($url_parts['host']) ? $url_parts['host'] : '127.0.0.1';
$user = !empty($url_parts['user']) ? $url_parts['user'] : 'root';
$pass = $url_parts['pass'] ?? '';
$db = !empty($url_parts['path']) ? ltrim($url_parts['path'], '/') : 'student_auth_db';
$port = !empty($url_parts['port']) ? $url_parts['port'] : 3306;

$conn = @new mysqli($host, $user, $pass, $db, $port);

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
