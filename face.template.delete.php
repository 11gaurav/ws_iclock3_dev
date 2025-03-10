<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
// header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

// Make sure that it is a DELETE request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'DELETE') != 0){
    $response = array(
        'Result' => "false",
        'Message' => "Request method must be DELETE!",
        'Method' => $_SERVER['REQUEST_METHOD']
    );
    echo json_encode($response);
    exit;
}

// Make sure that the content type of the DELETE request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    $response = array(
        'Result' => "false",
        'Message' => "Content type must be: application/json",
        'Method' => $contentType
    );
    echo json_encode($response);
    exit;
}

// Receive the RAW post data.
$content = file_get_contents("php://input");

// Attempt to decode the incoming RAW post data from JSON.
$obj = json_decode($content);

// Validate input (you can implement custom validation as needed)
require_once("validate.php");
// $input = Validate::isValid();

// Connect to database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

// Ensure the employee_id is present
if (empty($obj->employee_id)) {
    $response = array(
        'Success' => false,
        'Message' => 'Employee ID is required to delete data'
    );
    echo json_encode($response);
    exit;
}

if (!empty($obj->employee_id)) {
    $query = "DELETE FROM employee_mobile_face 
             WHERE employee_id = '" . $obj->employee_id . "'";
} 

$res = mysqli_query($connection, $query);

if($res) {
    $response = array(
        'Success' => true,
        'Message' => 'Employee data deleted successfully'
    );
} else {
    $response = array(
        'Success' => false,
        'Message' => 'Failed to delete employee data'
    );
}

echo json_encode($response);
?>
