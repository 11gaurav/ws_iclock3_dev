<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
// header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

//Make sure that it is a POST request. 
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
$response = array(
  'Result' => "false",
  'Message' => "Request method must be POST!",
  'Method' => $_SERVER['REQUEST_METHOD']
);
echo json_encode($response);
//  throw new Exception('Request method must be POST!');
}
 
//Make sure that the content type of the POST request has been set to application/json
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

//Receive the RAW post data.
$content = file_get_contents("php://input");
//Attempt to decode the incoming RAW post data from JSON.
$obj = json_decode($content);


require_once("validate.php");
//$input = Validate::isValid();
// $list = Validate::isContact();
// $list = array();
 
// Connect to database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

//Get awards
// echo($obj->AppID);
// echo($obj->Name);
// echo($obj->Roles);
// echo($obj->Number);
// echo($obj->Email);
// echo($obj->NumberPublic);
// echo($obj->EmailPublic);


// reader_id = phone number from clocking
// sn = phone number from clocking
// seen = 2023-01-01 01:01:01 ( if possible update to clocking time and date contained in attendance table, field clock )

$query = "INSERT INTO reader (sn, name, site_id, stamp, delay, ttimes, opstamp, seen, zone, password_exempted, transflag, cellphone) VALUES ('" . $obj->phone_number ."', '9999', '999999', '9999', 1, '', '9999', '" . $obj->clock_time ."', 2, 'Yes', '999999', '')";


$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
	updateSeenStatus($obj->sn, $connection);


if ($res) {
  $readerId = mysqli_insert_id($connection);
  $response = array(
    'Result' => "Success",
    'reader_id' => $readerId
  );
} else {
  $response = array(
    'Result' => "Error"
  );
}

// $response = array(
//   'Result' => "Success"
// );

echo json_encode($response);