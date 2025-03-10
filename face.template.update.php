<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');    // Cache for 1 day
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

// Ensure the request method is POST
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
	$response = array(
		'Result' => "false",
		'Message' => "Request method must be POST!",
		'Method' => $_SERVER['REQUEST_METHOD']
	);
	echo json_encode($response);
	exit();
	}
	 

// Ensure the content type of the request is application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') != 0) {
    $response = array(
        'Result' => "false",
        'Message' => "Content type must be: application/json",
        'Method' => $contentType
    );
    echo json_encode($response);
		exit();
}

// Receive the RAW post data
$content = file_get_contents("php://input");

// Decode the JSON data
$obj = json_decode($content);

// Check if the required fields are provided
if (!isset($obj->employee_id)) {
    $response = array(
        'Result' => "false",
        'Message' => "Missing employee id"
    );
    echo json_encode($response);
}

// Connect to the database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

$check_duplicate_face_id = "SELECT COUNT(*) AS count FROM employee_mobile_face WHERE face_id = '" . $obj->face_id ."' and employee_id!='" . $obj->employee_id . "'";
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

// Prepare the SQL query to update the record
$query = "UPDATE employee_mobile_face 
          SET 
            face_id = '" . $obj->face_id . "',
            face_data = '" . $obj->face_data . "',
            template = '" . $obj->template . "',
            site_id = '" . $obj->site_id . "',
            created_at = '" . $obj->created_at . "'
          WHERE employee_id = '" . $obj->employee_id . "'";

// Execute the query
$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

// Check if the update was successful
if ($res) {
    $response = array(
        'Success' => true,
        'Message' => 'Record updated successfully'
    );
} else {
    $response = array(
        'Success' => false,
        'Message' => 'Error updating the record'
    );
}

// Return the response as JSON
echo json_encode($response);
?>
