<?php
// login.php
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

// Handle logout action
if(isset($_POST['action']) && $_POST['action'] == 'logout') {
    if(isset($_POST['session_token'])) {
        $token = $_POST['session_token'];
        $stmt = $conn->prepare("UPDATE users SET session_token = NULL WHERE session_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode(["status" => "success", "message" => "Logged out"]);
    $conn->close();
    exit();
}

if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // prepare statement checking email
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // verify password
        if(password_verify($password, $row['password'])) {
            
            $user_id = $row['id'];
            
            // Create a random session token
            $session_token = bin2hex(random_bytes(16)); 
            
            // Save token to database
            $update_stmt = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $session_token, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            echo json_encode([
                "status" => "success", 
                "user_id" => $user_id,
                "session_token" => $session_token
            ]);
            
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Please send email and password"]);
}

$conn->close();
?>
