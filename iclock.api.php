<?php

Class IClockAPI{

	function sendSMS($servername,$phonenumber, $message) {
    // $url = "http://".$servername."/iclock/api/Iclock_api/send_sms";
    $url = "http://178.62.72.140/iclock/api/Iclock_api/send_sms";
    $data = json_encode([
        "mobile_no" => $phonenumber,
				"message" => $message	
    ]);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Content-Type: application/json"
		]);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

}

?>