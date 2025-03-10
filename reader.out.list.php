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


  // $query = "INSERT INTO attendance (employee_pin,employee_id,reader_id,clock,mode,status,work,job,downloaded,mask,temperature,clock_gps,clock_photo) VALUES ('" . $obj->employee_pin . "', " . $obj->employee_id .", " . $obj->reader_id . ", '" . $obj->clock ."', 15, " . $obj->status . ", '', '', 'No',  0, 0.0, '" . $obj->clock_gps . "', '" . $obj->clock_photo ."')";

	$query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
	$result = mysqli_query($connection, $query);

	require_once("commonFunction.php");
	updateSeenStatus($obj->sn, $connection);
	
	// site_id, site_code, name
	
	$query = "SELECT out_reader_id AS 'ID', out_reader  AS 'Reader' FROM out_reader_trans  where out_reader_trans.reader_access_groups_id = '" . $obj->AccessGroupID . "' order by order_id asc;";

	$messages = array();
	$result = mysqli_query($connection, $query);

	while ($row = mysqli_fetch_assoc($result)) {
		$messages[] = $row;
	}
	
	$response = array(
		'Success' => true,
		'Messages' => $messages
	);

echo json_encode($response);

?>