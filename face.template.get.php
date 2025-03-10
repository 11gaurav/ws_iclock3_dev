<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');    // Cache for 1 day
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

header('Content-Type: application/json');

// Ensure the request method is POST
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    $response = array(
        'Result' => "false",
        'Message' => "Request method must be POST!",
        'Method' => $_SERVER['REQUEST_METHOD']
    );
    echo json_encode($response);
}

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


include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

$query = "SELECT * FROM employee_mobile_face where site_id=". $obj->site_id .";";

$res = mysqli_query($connection, $query);

require_once("commonFunction.php");
updateSeenStatus($obj->sn, $connection);

if ($res) {
    $data = array();
    
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    
    if (count($data) > 0) {
        $response = array(
            'Success' => true,
            'Data' => $data
        );
    } else {
        $response = array(
            'Success' => false,
            'Message' => 'No records found'
        );
    }
} else {
    $response = array(
        'Success' => false,
        'Message' => 'Error fetching data from the database'
    );
}

echo json_encode($response);
?>
