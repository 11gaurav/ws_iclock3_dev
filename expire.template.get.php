<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

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

$query = "SELECT expiry_time FROM time_expire where site_id='". $obj->site_id ."';";

$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

if ($res) {
    $row = mysqli_fetch_assoc($res);
		$row = (int)$row['expiry_time'];

    if ($row) {
        $response = array(
            'Success' => true,
            'expiry' => $row
        );
    } else {
        $response = array(
            'Success' => false,
            'Message' => 'No records found'
        );
    }
} else {
    $response = array(
        'Success' => false,
        'Message' => 'Error fetching data from the database'
    );
}

echo json_encode($response);
?>
