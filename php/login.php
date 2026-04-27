<?php
// login.php
header('Content-Type: application/json');

// Handle logout action
if(isset($_POST['action']) && $_POST['action'] == 'logout') {
    if(isset($_POST['session_token'])) {
        $token = $_POST['session_token'];
        // connect to redis to remove session
        try {
            if (class_exists('Redis')) {
                $redis = new Redis();
                if (@$redis->connect('127.0.0.1', 6379, 1)) {
                    $redis->del("session:" . $token);
                }
            }
        } catch (Throwable $e) {
            // just ignore if redis fails
        }
    }
    
    // Fallback: clear PHP session
    session_start();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Logged out"]);
    exit();
}

// Database connection using MYSQL_PUBLIC_URL
$db_url = getenv("MYSQL_PUBLIC_URL");
$url_parts = parse_url($db_url);

$host = $url_parts['host'];
$user = $url_parts['user'];
$pass = $url_parts['pass'];
$db = ltrim($url_parts['path'], '/');
$port = $url_parts['port'];

$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
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
            
            $used_redis = false;
            try {
                if (class_exists('Redis')) {
                    $redis = new Redis();
                    // Connect with short timeout
                    if (@$redis->connect('127.0.0.1', 6379, 1)) {
                        // Store token in redis with user_id as value, expires in 1 hour
                        $redis->setex("session:" . $session_token, 3600, $user_id);
                        $used_redis = true;
                    }
                }
            } catch(Throwable $e) {
                // Redis unavailable, fallback to PHP session
            }

            // Fallback to PHP session
            if (!$used_redis) {
                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['session_token'] = $session_token;
            }
            
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
