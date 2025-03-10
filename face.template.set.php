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
$obj->created_at = str_replace(': ', '', $obj->created_at);
$check_duplicate_face_id = "SELECT COUNT(*) AS count FROM employee_mobile_face WHERE face_id = '" . $obj->face_id ."'";


$check_result = mysqli_query($connection, $check_duplicate_face_id);
$row = mysqli_fetch_assoc($check_result);

if ($row['count'] > 0) {
    $response = array(
        'Success' => false,
        'Message' => "Duplicate face_id detected."
    );
    echo json_encode($response);
    exit;
}

$query = "INSERT INTO employee_mobile_face (employee_id, face_id, face_data, template, site_id, created_at) VALUES ( '" . $obj->employee_id . "','" . $obj->face_id . "','" . $obj->face_data . "','" . $obj->template . "', '" . $obj->site_id . "', '" . $obj->created_at . "');";


$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);


if ($res) {
    $response = array(
        'Success' => true,
        'Message' => "Data inserted successfully."
    );
} else {
    $response = array(
        'Success' => false,
        'Message' => "Failed to insert data."
    );
}

echo json_encode($response);

