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

Logger::info($content);
// Connect to database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);
function generateAccessCode($length = 4) {
  $characters = '123456789';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

// echo $obj->visit_date;
// echo $obj->created_at;
$obj->visit_date = str_replace(': ', '', $obj->visit_date);
$obj->created_at = str_replace(': ', '', $obj->created_at);


// $query = "INSERT INTO visitors_access_code (visitors_name, visitors_mobile_no, visit_date, reader_id, site_id, visit_multi_times, category_id, sub_category_id, access_hours_duration, start_date, end_date, created_by, created_at) VALUES ('" . $obj->visitors_name . "', '" .$obj->visitors_mobile_no . "', '" .$obj->visit_date . "', '" . 0 . "', '" . $obj->site_id ."', '" . $obj->visit_multi_times ."', '" . $obj->category_id ."', '" . $obj->sub_category_id ."', '" . $obj->access_hours_duration ."', '" . $obj->start_date ."', '" . $obj->end_date ."', '" . $obj->created_by ."', '" . $obj->created_at ."');"
$query = "INSERT INTO visitors_access_code (access_code, visitors_name, visitors_mobile_no, visit_date, reader_id, site_id, visit_multi_times, category_id, sub_category_id, access_hours_duration, start_date, end_date, created_by, created_at, visitor_id_photo, visitor_number_plate_photo, emp_m_v_address) VALUES ( '" . $obj->access_code . "','" . $obj->visitors_name . "', '" . $obj->visitors_mobile_no . "', '" . $obj->visit_date . "', '0', '" . $obj->site_id . "', '" . $obj->visit_multi_times . "', '" . $obj->category_id . "', '" . $obj->sub_category_id . "', '" . $obj->access_hours_duration . "', '" . $obj->start_date . "', '" . $obj->end_date . "', '" . $obj->created_by . "', '" . $obj->created_at . "', '" . $obj->idImage . "', '" . $obj->carRegImage . "', '" . $obj->emp_m_v_address . "');";
// echo $query;

$res = mysqli_query($connection, $query);
$response = array(
  'Result' => "Success"
);
// echo $res;
// echo $obj->reader_id;
// echo $res AND $obj->reader_id != -1;
if( $obj->reader_id != -1) {
  if ($res) {
    // echo "IF Entered";
    $employeeQuery = "INSERT INTO employee (pin, password, name, mobile_number, site_id, visit_multi_times, username, access_group, category_id, sub_category_id, is_antipass, start_date, end_date) 
    VALUES ('". generateAccessCode() ."', '" . $obj->access_code . "', '" . $obj->visitors_name . "', '" . $obj->visitors_mobile_no . "', '" . $obj->site_id . "', '" . $obj->visit_multi_times . "', '" . $obj->visitors_name . "', '" . $obj->reader_id . "', '" . $obj->category_id . "', '" . $obj->sub_category_id . "', 'No', '" . $obj->start_date . "', '" . $obj->end_date . "' )";
    $employeeResponse = mysqli_query($connection, $employeeQuery);

    $insertId = mysqli_insert_id($connection);

    $query = "INSERT INTO mobile_data (visitors_access_code_id, access_group_id, qrcode) VALUES ( '" . $insertId . "','" . $obj->reader_id . "', '" . $obj->qr_code . "');";
    // echo $query;

    $res = mysqli_query($connection, $query);
    // echo $res;
    
    // Retrieve reader access group data
    $readerAccessGroupQuery = "SELECT * FROM `reader_access_groups` WHERE site_id = '" . $obj->site_id . "' AND reader_access_groups_id = '" . $obj->reader_id . "'";
    $readerAccessGroupResult = mysqli_query($connection, $readerAccessGroupQuery);

    $inReaderTransactions = array();
    $outReaderTransactions = array();
    $exitReaderTransactions = array();

    while ($readerAccessGroup = mysqli_fetch_assoc($readerAccessGroupResult)) {
        // Query transactions for the specific reader access group
        $inReaderQuery = "SELECT * FROM `in_reader_trans` WHERE site_id = '" . $readerAccessGroup["site_id"] . "' AND reader_access_groups_id = '" . $readerAccessGroup["reader_access_groups_id"] . "'";
        $outReaderQuery = "SELECT * FROM `out_reader_trans` WHERE site_id = '" . $readerAccessGroup["site_id"] . "' AND reader_access_groups_id = '" . $readerAccessGroup["reader_access_groups_id"] . "'";
        $exitReaderQuery = "SELECT * FROM `exit_reader_trans` WHERE site_id = '" . $readerAccessGroup["site_id"] . "' AND reader_access_groups_id = '" . $readerAccessGroup["reader_access_groups_id"] . "'";
        
        // Execute each transaction query
        $inReaderResult = mysqli_query($connection, $inReaderQuery);
        $outReaderResult = mysqli_query($connection, $outReaderQuery);
        $exitReaderResult = mysqli_query($connection, $exitReaderQuery);

        // Append data to arrays
        $inReaderTransactions = array_merge($inReaderTransactions, mysqli_fetch_all($inReaderResult, MYSQLI_ASSOC));
        $outReaderTransactions = array_merge($outReaderTransactions, mysqli_fetch_all($outReaderResult, MYSQLI_ASSOC));
        $exitReaderTransactions = array_merge($exitReaderTransactions, mysqli_fetch_all($exitReaderResult, MYSQLI_ASSOC));
    }

    // Merge all transaction arrays
    $allTransactions = array_merge($inReaderTransactions, $outReaderTransactions, $exitReaderTransactions);

    // Remove duplicate entries
    $uniqueTransactions = array_unique($allTransactions, SORT_REGULAR);

    $command = "DATA USER PIN=" . $obj->access_code . "\tName=" . $obj->visitors_name . "\tPasswd=" . "" . "\tCard=" . $obj->access_code . "\tGrp=" . "" . "\tTZ=" . "" . "\tPri=" . "";

    $readers = array();
    foreach ($uniqueTransactions as $key => $row) {
      // Check for the existence of each key and set $reader_id accordingly

      if (isset($row['in_reader'])) {
        $readers[] = $row['in_reader'];
      } elseif (isset($row['out_reader'])) {
        $readers[] = $row['out_reader'];
      } elseif (isset($row['exit_reader'])) {
        $readers[] = $row['exit_reader'];
      }
    }

    foreach (array_unique($readers) as $key => $reader_id) {
      $reader_command_query = "INSERT INTO reader_command (reader_id, command, status, sourceinfo) 
        VALUES ('" . $reader_id . "', '" . $command . "', 'Active', 'ws_iclock3');";
      mysqli_query($connection, $reader_command_query); 
    }  

    require_once("commonFunction.php");
	  updateSeenStatus($obj->sn, $connection);

    $response = array(
      'Result' => "Success"
    );
  } else {
    $response = array(
      'Result' => "Error"
    );
  }
}

echo json_encode($response);

