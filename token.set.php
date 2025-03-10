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

// Connect to database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

// echo $obj->visit_date;
// echo $obj->created_at;
$obj->visit_date = str_replace(': ', '', $obj->visit_date);
$obj->created_at = str_replace(': ', '', $obj->created_at);


// $query = "INSERT INTO visitors_access_code (visitors_name, visitors_mobile_no, visit_date, reader_id, site_id, visit_multi_times, category_id, sub_category_id, access_hours_duration, start_date, end_date, created_by, created_at) VALUES ('" . $obj->visitors_name . "', '" .$obj->visitors_mobile_no . "', '" .$obj->visit_date . "', '" . 0 . "', '" . $obj->site_id ."', '" . $obj->visit_multi_times ."', '" . $obj->category_id ."', '" . $obj->sub_category_id ."', '" . $obj->access_hours_duration ."', '" . $obj->start_date ."', '" . $obj->end_date ."', '" . $obj->created_by ."', '" . $obj->created_at ."');"
$query = "INSERT INTO visitors_access_code (access_code, visitors_name, visitors_mobile_no, visit_date, reader_id, site_id, visit_multi_times, category_id, sub_category_id, access_hours_duration, start_date, end_date, created_by, created_at) VALUES ( '" . $obj->access_code . "','" . $obj->visitors_name . "', '" . $obj->visitors_mobile_no . "', '" . $obj->visit_date . "', '0', '" . $obj->site_id . "', '" . $obj->visit_multi_times . "', '" . $obj->category_id . "', '" . $obj->sub_category_id . "', '" . $obj->access_hours_duration . "', '" . $obj->start_date . "', '" . $obj->end_date . "', '" . $obj->created_by . "', '" . $obj->created_at ."');";
// echo $query;

$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
	updateSeenStatus($obj->sn, $connection);

$response = array(
  'Result' => "Success"
);

echo json_encode($response);

