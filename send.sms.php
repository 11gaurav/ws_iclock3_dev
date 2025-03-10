<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
// header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

include("validate.php");

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
	$response = array(
		'Result' => "false",
		'Message' => "Request method must be POST!",
		'Method' => $_SERVER['REQUEST_METHOD']
	);
	echo json_encode($response);
	//  throw new Exception('Request method must be POST!');
	}

	$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
$response = array(
  'Result' => "false",
  'Message' => "Content type must be: application/json",
  'Method' => $contentType
);

echo json_encode($response);
// throw new Exception('Content type must be: application/json');
}
$success = false;
// Receive the RAW post data.
$content = file_get_contents("php://input");
// Attempt to decode the incoming RAW post data from JSON.
$obj = json_decode($content);

include("iclock.api.php");
$icApi = new IClockAPI();

$result = $icApi->sendSMS($obj->ServerName,$obj->PhoneNumber,$obj->Message);

echo $result;

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

// if ($result->status) {
// 	$response = array(
// 		'Success' => true,
// 		'Message' => 'SMS sent successfully'
// 	);
// } else {
// 	$response = array(
// 		'Success' => false,
// 		'Message' => 'Error sending SMS: ' . $result
// 	);
// }

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
// echo json_encode($response);

?>