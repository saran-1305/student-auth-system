<?php
// profile.php
header('Content-Type: application/json');

// get inputs
// Note: $_REQUEST is used for fetching both GET and POST for simplicity in this student project
$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
$session_token = isset($_REQUEST['session_token']) ? $_REQUEST['session_token'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (empty($user_id) || empty($session_token)) {
    echo json_encode(["status" => "error", "message" => "Missing credentials"]);
    exit();
}

// 1. Validate session with Redis first
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        // Check if key exists
        $stored_id = $redis->get("session:" . $session_token);

        if (!$stored_id || $stored_id != $user_id) {
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit();
        }
    }
} catch (Throwable $e) {
    // If Redis fails or is unavailable, skip validation and continue execution (fallback mode)
}

// 2. Connect to MongoDB using composer autoloader
// Assuming standard student setup where composer vendor is in the root or php folder
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    echo json_encode(["status" => "error", "message" => "MongoDB library not found. Run 'composer require mongodb/mongodb'"]);
    exit();
}

// 3. Check for PHP MongoDB Extension
if (!extension_loaded('mongodb')) {
    echo json_encode(["status" => "error", "message" => "PHP MongoDB extension (php_mongodb.dll) is missing. Please install it in XAMPP and enable it in php.ini."]);
    exit();
}

try {
    // connect to local mongodb
    $mongoClient = new MongoDB\Client("mongodb+srv://saran:saran130507@cluster0.8jpaw5j.mongodb.net/?appName=Cluster0");
    $collection = $mongoClient->user_auth->profiles;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "MongoDB connection error."]);
    exit();
}

// 3. Handle actions
if ($action == 'get_profile') {
    // Find profile using user_id
    $profile = $collection->findOne(['user_id' => $user_id]);

    if ($profile) {
        // Return existing data
        $data = [
            'age' => $profile['age'] ?? '',
            'dob' => $profile['dob'] ?? '',
            'contact' => $profile['contact'] ?? ''
        ];
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        // user hasn't set profile yet
        echo json_encode(["status" => "success", "data" => null]);
    }
} else if ($action == 'update_profile') {
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';

    // Update or Insert into mongo collection
    try {
        $updateResult = $collection->updateOne(
            ['user_id' => $user_id], // search criteria
            [
                '$set' => [             // new data
                    'age' => $age,
                    'dob' => $dob,
                    'contact' => $contact
                ]
            ],
            ['upsert' => true] // create if not exists
        );

        // Give a success response regardless if they modified it or just saved the exact same data
        echo json_encode(["status" => "success", "message" => "Profile saved!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error saving profile."]);
    }
}
?>