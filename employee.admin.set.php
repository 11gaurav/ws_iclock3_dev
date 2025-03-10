<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

// Make sure that it is a POST request. 
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    $response = array(
        'Result' => "false",
        'Message' => "Request method must be POST!",
        'Method' => $_SERVER['REQUEST_METHOD']
    );
    echo json_encode($response);
    // throw new Exception('Request method must be POST!');
}

// Make sure that the content type of the POST request has been set to application/json
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

$content = file_get_contents("php://input");

$obj = json_decode($content);

require_once("validate.php");

include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

$check_if_employee_exists = "SELECT COUNT(*) AS count FROM employee WHERE employee_id = '" . $obj->employee_id . "' and site_id = '" . $obj->site_id . "';";

$check_result = mysqli_query($connection, $check_if_employee_exists);
$row = mysqli_fetch_assoc($check_result);
$admin_status = $obj->admin ? '14' : 0;
$reposnse_message = $obj->admin ? 'Employee set as admin' : 'Employee removed as admin'; 

if ($row['count'] < 1) {
    $response = array(
        'Success' => false,
        'Message' => "No record found."
    );
    echo json_encode($response);
    exit;
} else {
    $query = "UPDATE employee 
        SET priv = '".$admin_status."'
        WHERE employee_id = '" . $obj->employee_id . "' and site_id = '" . $obj->site_id . "';";

    $res = mysqli_query($connection, $query);

    if ($res) {
        $response = array(
            'Success' => true,
            'Message' => $reposnse_message
        );
    } else {
        $response = array(
            'Success' => false,
            'Message' => "Failed to set employee as admin."
        );
    }
}

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

echo json_encode($response);
