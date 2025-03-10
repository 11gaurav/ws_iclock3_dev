<?php

include("validate.php");
//$input = Validate::isValid();
// $list = Validate::isContact();
// $list = array();

// Connect to database
include("connection.php");
$db = new Database();
$connection =  $db->getConnstring();

$query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
$result = mysqli_query($connection, $query);

//Get awards
// echo($_GET["AppID"]);

if (empty($_GET["AppID"])) {

  $query = "SELECT Title,Message,Date FROM Notifications order by ID desc";

  $messages = array();
  $result = mysqli_query($connection, $query);

  while ($row = mysqli_fetch_array($result)) {
    $obj = array(
      'Title' => $row['Title'],
      'Message' => $row['Message'],
      'Date' => $row['Date']
    );
    array_push($messages, $obj);
  }

  $response = array(
    'Messages' => $messages
  );
} else {
  $query = "SELECT Title,Message,Date FROM Notifications WHERE App=" . $_GET["AppID"] . " order by ID desc";

  $messages = array();
  $result = mysqli_query($connection, $query);

  while ($row = mysqli_fetch_array($result)) {
    $obj = array(
      'Title' => $row['Title'],
      'Message' => $row['Message'],
      'Date' => $row['Date']
    );
    array_push($messages, $obj);
  }

  $response = array(
    'Messages' => $messages
  );
}

// echo($messages);

$query = "COMMIT;";
$result = mysqli_query($connection, $query);

foreach ($response as $key => $value) {
  $base64Data[$key] = $response[$key];
  //$base64Data[$key]['Image'] = base64_encode($response[$key]['Image']);
}

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
} else {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: X-Requested-With");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

header('Content-Type: application/json');
echo json_encode(Validate::utf8ize($base64Data));
