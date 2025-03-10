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

if (empty($_GET["AppID"]) && empty($_GET["Topic"]) && empty($_GET["Title"]) && empty($_GET["Message"])) {
  $response = array(
    'Messages' => 'Unable to send Notification. Please ensure that the info is correctly set.'
  );
} else {
  $query = "INSERT INTO Notifications (App,Topic,Title,Message) VALUES (" . $_GET["AppID"] . ", '/topics/" . $_GET["Topic"] . "' ,'" . $_GET["Title"] . "','" . $_GET["Message"] . "')";
  print('query');
  print($query);

  $messages = array();
  $result = mysqli_query($connection, $query);
  
  print(result);
  
  $query2 = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
  $result2 = mysqli_query($connection, $query2);

  $query2 = "SELECT ServerKey,SenderID FROM Apps WHERE ID = " . $_GET["AppID"] . ";"
  print('query2');
  print($query2);

  $messages2 = array();
  $result2 = mysqli_query($connection, $query2);
  
  print(result2);
  $serverKey = "";
  $senderID = ""
  
  while ($row = mysqli_fetch_array($result)) {
    $serverKey => $row['ServerKey'],
    $senderID  => $row['SenderID'],
  }


	if ($_GET["serverkey"]!="") {
		
		$notification =   array(
                                'title'=>$_GET["Title"],
                               // 'text'=>$_GET["Message"],
                                'body'=>$_GET["Message"],
                                'sound' => $_GET["sound"],
                            );
		$data =   array(
                                'landing_page'=>"results"                              
                            );
		$message =   array(
								'notification'=>$notification,
								'data'=>$data,
                                'to'=>"./topics/".$_GET["Topic"],
                                'priority'=>"high",
                                'restricted_package_name'=>""                      
                            );						
	
		$hackmessage = str_replace("\/","/",json_encode($message,true));
		
		$url = curl_init("https://fcm.googleapis.com/fcm/send");
		$header=array('Content-Type: application/json',
						"Authorization: key=".$serverKey,
						"Sender: id=".$senderID);
						
		curl_setopt($url, CURLOPT_HTTPHEADER, $header);
		curl_setopt($url, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false );

		curl_setopt($url, CURLOPT_POST, 0);
		curl_setopt($url, CURLOPT_POSTFIELDS,$hackmessage);

		//DEBUG purposes:
		//echo $hackmessage;
		
		$result =   curl_exec($url);
		curl_close($url);

		if ($result === FALSE) {
			echo "cURL fail";
			//DEBUG purposes:
			//echo "Curl failed: " . curl_error($url));
		}
		else{
			$result =   json_decode($result);
			if($result->success ===1){
				print_r($result);
			}
			else{
				print_r($result);
			}
		}
		
		// echo "<p>Thank you! Please send another notification</p>";
	}
 

  $response = array(
    'Messages' => $result
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
// if (isset($_SERVER['HTTP_ORIGIN'])) {
//   // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
//   // you want to allow, and if so:
//   header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
//   header('Access-Control-Allow-Credentials: true');
//   header('Access-Control-Max-Age: 86400');    // cache for 1 day
// } else {
//   header('Access-Control-Allow-Origin: *');
//   header('Access-Control-Allow-Methods: GET, POST');
//   header("Access-Control-Allow-Headers: X-Requested-With");
//   header('Access-Control-Allow-Credentials: true');
//   header('Access-Control-Max-Age: 86400');    // cache for 1 day
// }

header('Content-Type: application/json');
echo json_encode(Validate::utf8ize($base64Data));
 