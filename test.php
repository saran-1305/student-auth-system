<?php
$autoload_path = __DIR__ . '/vendor/autoload.php';
require_once $autoload_path;

try {
    $mongoClient = new MongoDB\Client("mongodb+srv://saran:saran130507@cluster0.8jpaw5j.mongodb.net/?appName=Cluster0");
    $collection = $mongoClient->user_auth->profiles;

    $user_id = '1';
    $age = '25';
    $dob = '2000-01-01';
    $contact = '0987654321';

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

    echo json_encode(["status" => "success", "message" => "Profile saved!"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
