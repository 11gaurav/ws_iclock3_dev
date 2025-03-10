<?php

include("validate.php");

$success = false;
// Receive the RAW post data.
$content = file_get_contents("php://input");
// Attempt to decode the incoming RAW post data from JSON.
$obj = json_decode($content);

$ip_list = array("167.99.92.164", "178.62.72.140", "165.22.205.179", "138.68.128.112", "161.35.149.243","165.227.237.95", "162.241.119.77", "138.68.169.19", "178.62.10.202"); // list of servers that are valid

foreach ($ip_list as $allowed_ip) {
	// Check if the IP address matches the allowed IP
	if ($obj->ServerName == $allowed_ip) {
		$success = true;
		break;
	}
}

$response = array(
  'Success' => $success
);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
} else {
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
	header("Access-Control-Allow-Origin: *");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');     // cache for 1 day
}

header('Content-Type: application/json');
echo json_encode($response);

?>