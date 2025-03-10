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


	$query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
	$result = mysqli_query($connection, $query);
	
	// site_id, site_code, name
	
	$query = "SELECT 
  employee_id, name, category_id, sub_category_id, site_id, emp_type, emp_m_address
  FROM iclockdb.employee 
  where username = '" . $obj->Username . "' and password =  '" . $obj->Password . "';";

  // Select 
	// site_id, name
	// from site 
	// where site_id = " . $obj->Username . " and site_password = " . $obj->Password . "
	// order by site_id asc;";
	// echo $query;

	$messages = array();
	$result = mysqli_query($connection, $query);

	require_once("commonFunction.php");
	updateSeenStatus($obj->sn, $connection);

	while ($row = mysqli_fetch_array($result)) {
		$obj = array(
			'EmpID' => $row['employee_id'],
			'Name' => $row['name'],
      'CatID' => $row['category_id'],
      'SubCatID' => $row['sub_category_id'],
      'SiteID' => $row['site_id'],
      'EmpType' => $row['emp_type'],
      'emp_m_address' => $row['emp_m_address']
		);
		array_push($messages, $obj);
	}
	
	$response = array(
		'Success' => true,
		'Messages' => $messages
	);

echo json_encode($response);

?>