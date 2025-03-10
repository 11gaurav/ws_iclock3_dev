<?php

	if ($_GET["serverkey"]!="") {
		
		$notification =   array(
                                'title'=>$_GET["title"],
                               // 'text'=>$_GET["message"],
                                'body'=>$_GET["message"],
                                'sound' => $_GET["sound"],
                            );
		$data =   array(
                                'landing_page'=>"results"                              
                            );
		$message =   array(
								'notification'=>$notification,
								'data'=>$data,
                                'to'=>"./topics/".$_GET["topic"],
                                'priority'=>"high",
                                'restricted_package_name'=>""                      
                            );						
	
		$hackmessage = str_replace("\/","/",json_encode($message,true));
		
		$url = curl_init("https://fcm.googleapis.com/fcm/send");
		$header=array('Content-Type: application/json',
						"Authorization: key=".$_GET["serverkey"],
						"Sender: id=".$_GET["senderid"]);
						
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
		
		echo "<p>Thank you! Please send another notification</p>";
	}
	
	//CHANGE here
	$server_key = "ABCDEF";
	$sender_id = 1234;
	//FINISH Changes
	
	echo "<form action=\"/cmce-api/send.notification.php\">";
	echo "<input type=\"hidden\" name=\"serverkey\" value=\"".$server_key."\">";
	echo "<input type=\"hidden\" name=\"senderid\" value=\"".$sender_id."\">";
	echo "<p>Topic:<br><input type=\"text\" name=\"topic\" size=\"40\" value=\"".$_GET["topic"]."\"></p>";
	echo "<p>Title:<br><input type=\"text\" name=\"title\" size=\"40\" value=\"".$_GET["title"]."\"></p>";
	echo "<p>Sound:<br><input type=\"text\" name=\"sound\" size=\"40\" value=\"".$_GET["sound"]."\"></p>";
	echo "<p>Message:<br><textarea name=\"message\" rows=\"5\" cols=\"38\">".$_GET["message"]."</textarea></p>";
	echo "<p><input type=\"submit\" value=\"Submit\"></p>";
	echo "</form>";

?>