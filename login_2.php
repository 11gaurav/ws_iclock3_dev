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

	$messages = array();
	$query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
	$result = mysqli_query($connection, $query);

	//query for to fetch data
	$query = "SELECT site_id, site_code, name FROM site WHERE site_code = ? AND site_password = ? ORDER BY site_id ASC";
	$stmt = mysqli_prepare($connection, $query);
	mysqli_stmt_bind_param($stmt, "ss", $obj->SiteCode, $obj->Password);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	require_once("commonFunction.php");
	updateSeenStatus($obj->sn, $connection);

	while ($row = mysqli_fetch_array($result)) {
		$obj = array(
			'ID' => $row['site_id'],
			'Name' => $row['name'],
      		'SiteCode' => $row['site_code'],
		);
		array_push($messages, $obj);
	}
	
	$response = array(
		'Success' => true,
		'Messages' => $messages
	);

echo json_encode($response);

?>