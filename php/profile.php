<?php
// profile.php
header('Content-Type: application/json');

function getSession($session_token) {
    $url = 'https://sterling-gecko-97057.upstash.io/get/session:' . $session_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer gQAAAAAAAXshAAIgcDFhYjBlMDI0MzA5NWU0ZjM5OWM4MDU5MWE0YmEwMDU0MQ"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['result']) && $data['result'] !== null) {
            return $data['result'];
        }
    }
    return null;
}

// get inputs
// Note: $_REQUEST is used for fetching both GET and POST for simplicity in this student project
$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
$session_token = isset($_REQUEST['session_token']) ? $_REQUEST['session_token'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (empty($user_id) || empty($session_token)) {
    echo json_encode(["status" => "error", "message" => "Missing credentials"]);
    exit();
}

// 1. Validate session with Upstash Redis
$is_authorized = false;

$stored_user_id = getSession($session_token);
if ($stored_user_id !== null && $stored_user_id == $user_id) {
    $is_authorized = true;
}

if (!$is_authorized) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// 2. Handle actions
require_once __DIR__ . '/../vendor/autoload.php';

$mongoClient = new MongoDB\Client("mongodb+srv://saran:saran130507@cluster0.8jpaw5j.mongodb.net/?appName=Cluster0");
$profilesCollection = $mongoClient->user_auth->profiles;

if ($action == 'get_profile') {
    $profile = $profilesCollection->findOne(['user_id' => $user_id]);
    
    if ($profile) {
        $data = [
            'age' => $profile['age'] ?? '',
            'dob' => $profile['dob'] ?? '',
            'contact' => $profile['contact'] ?? ''
        ];
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "success", "data" => null]);
    }
} else if ($action == 'update_profile') {
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';

    try {
        $profilesCollection->updateOne(
            ['user_id' => $user_id],
            ['$set' => [
                'user_id' => $user_id,
                'age' => $age,
                'dob' => $dob,
                'contact' => $contact
            ]],
            ['upsert' => true]
        );
        echo json_encode(["status" => "success", "message" => "Profile saved!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}
?>