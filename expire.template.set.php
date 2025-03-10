<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');


function sendResponse($success, $message) {
    echo json_encode([
        'Success' => $success,
        'Message' => $message
    ]);
    exit;
}

// Validate the request method (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'Result' => 'false',
        'Message' => 'Request method must be POST!',
        'Method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strcasecmp($contentType, 'application/json') !== 0) {
    echo json_encode([
        'Result' => 'false',
        'Message' => 'Content type must be: application/json',
        'Method' => $contentType
    ]);
    exit;
}

$content = file_get_contents('php://input');
$obj = json_decode($content);

if ($obj->time < 1) {
    echo json_encode([
        'Success' => false,
        'Message' => 'Invalid Time'
    ]);
    exit;
}

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'Success' => false,
        'Message' => 'Invalid JSON received'
    ]);
    exit;
}

include("connection.php");

$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

$check_existed_site_id = "SELECT COUNT(*) AS count FROM time_expire WHERE site_id = '" . $obj->site_id . "'";
$check_result = mysqli_query($connection, $check_existed_site_id);
$row = mysqli_fetch_assoc($check_result);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

if ($row['count'] > 0) {
    $query = "UPDATE time_expire 
              SET 
              expiry_time = '" . $obj->time . "'
              WHERE site_id = '" . $obj->site_id . "';";
    $res = mysqli_query($connection, $query);

    if ($res) {
        sendResponse(true, "Time set successfully.");
    } else {
        sendResponse(false, "Failed to set expiry time.");
    }
} else {
    $query = "INSERT INTO time_expire (site_id, expiry_time) 
              VALUES ('" . $obj->site_id . "', '" . $obj->time . "');";

    $res = mysqli_query($connection, $query);

    if ($res) {
        sendResponse(true, "Time set successfully.");
    } else {
        sendResponse(false, "Failed to set expiry time.");
    }
}

echo json_encode($response);
?>
