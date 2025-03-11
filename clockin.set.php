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

include_once 'Logger.php';
require_once("validate.php");
//$input = Validate::isValid();
// $list = Validate::isContact();
// $list = array();

Logger::info($content);

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


// attendance_ID will be incremental
// employee_pin will be the pin that the employee clocked with
// employee_id wil be the same as the table employee, with field employee_id 
// reader_id will be the employee mobile number as linked in the employee table
// clock will contain the date and time
// mode will be 15
// status will be 0 for IN , 1 for OUT 
// work blank
// job blank 
// downlaoded will be no 
// created_at will be the clocking created date and time - use default value
// is_removed will be no 
// mask will be 0
// temperature will be 0.00 
// {employee_pin:pin,employee_id:empId,reader_id:emp.mobile_number,clock:"yyyy-MM-dd HH:mm:ss",status:0/1,clock_gps:'0.0, 0.0',clock_photo:base64}

$query = "INSERT INTO attendance (employee_pin,employee_id,reader_id,clock,mode,status,work,job,downloaded,mask,temperature,clock_gps,clock_photo, site_activity_code) VALUES ('" . $obj->employee_pin . "', " . $obj->employee_id .", " . $obj->reader_id . ", '" . $obj->clock ."', 15, " . $obj->status . ",'" . $obj->work ."', '', 'No',  0, 0.0, '" . $obj->clock_gps . "', '" . $obj->clock_photo ."','".$obj->site_activity_code. "')";

$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

$response = array(
  'Result' => "Success"
);

echo json_encode($response);