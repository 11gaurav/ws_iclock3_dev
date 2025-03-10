<?php

class Validate
{

  static function isValid()
  {

    //Make sure that it is a POST request.
    if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
      throw new Exception('Request method must be POST!');
    }

    //Make sure that the content type of the POST request has been set to application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (strcasecmp($contentType, 'application/json') != 0) {
      throw new Exception('Content type must be: application/json');
    }

    //Receive the RAW post data.
    $content = trim(file_get_contents("php://input"));

    //Attempt to decode the incoming RAW post data from JSON.
    $decoded = json_decode($content, true);

    //If json_decode failed, the JSON is invalid.
    if (!is_array($decoded)) {
      throw new Exception('Received content contained invalid JSON!');
    }

    return $decoded;
  }

  // static function isCandidate()
  // {

  //   $list = array(
  //     "IDNo" => "string", "ConsultantID" => "integer", "ContactVia" => "string",
  //     "FirstNames" => "string", "Surname" => "string", "Telephone" => "string",
  //     "Email" => "string", "DateOfBirth" => "datetime"
  //   );

  //   return $list;
  // }
  // static function isContact()
  // {

  //   $list = array(
  //     "IDNo" => "string", "ConsultantID" => "integer", "ContactVia" => "string",
  //     "FirstNames" => "string", "Surname" => "string", "Telephone" => "string",
  //     "Email" => "string", "DateOfBirth" => "datetime"
  //   );

  //   return $list;
  // }

  static function utf8ize($mixed)
  {
    if (is_array($mixed)) {
      foreach ($mixed as $key => $value) {
        $mixed[$key] = Validate::utf8ize($value);
      }
    } elseif (is_string($mixed)) {
      return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
  }

  static function formatTime($timevalue)
  {
    if (substr($timevalue, 0, 3) == "00:") {
      return substr($timevalue, 3, strlen($timevalue) - 3);
    } else {
      return $timevalue;
    }
  }

  static function formatDate($datevalue)
  {
    $datevalue = date_create($datevalue);
    return date_format($datevalue, "d M Y");
  }
}
