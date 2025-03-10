<?php

Class Database{

	/* Database connection start */
	/*
	var $servername = "sql17.cpt1.host-h.net";
	var $username = "fastar_2";
	var $password = "gzf2FNK8";
	var $dbname = "fastar_wp";
	*/

	// var $servername = "178.62.72.140";
// 	var $username = "mentech4_wsiclockdb";
// 	var $password = "jR-NWnN^x1{8";
// 	var $dbname = "mentech4_wsiclockdb";
	
	var $username = "root";
	var $password = "7oag*zfbw#jwss@ptbbf.kl??!@aeedHtOO$";
	var $dbname = "iclockdb";

	var $conn;

	function getConnstring($servername) {
		// $this->servername = $servername
		$con = mysqli_connect($servername, $this->username, $this->password, $this->dbname) or die("Connection failed: " . mysqli_connect_error());
		
		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		} 
		else {
			$this->conn = $con;
		}
		
		return $this->conn;
	}
	
	// function setServerName($sname) {
	// 	$this->servername = $sname
	// }

	static function getInsertQuery($tablename, $input, $list) {
		
		$sql = "INSERT INTO $tablename (";		
		foreach($list as $key=>$value) {
			$sql .= $key.",";
		}
		$sql = substr($sql, 0, -1).") VALUES (";		
		foreach($input as $key=>$value) {
			if (isset($list[$key])) {
				switch ($list[$key])
				{
					case "string":		
						$sql .= "'".$value."',";
						break;
						
					default:		
						$sql .= $value.",";
				}
			}
		}
		$sql = substr($sql, 0, -1).")";
		return $sql;
	}
	
	static function getUpdateQuery($tablename, $input, $list) {
		
		$sql = "UPDATE $tablename SET ";		
		foreach($input as $key=>$value) {
			if (isset($list[$key])) {
				switch ($list[$key])
				{
					case "string":		
						$sql .= $key." = '".$value."',";
						break;
						
					default:		
						$sql .= $key." = ".$value.",";
				}
			}
		}
		$sql = substr($sql, 0, -1)." WHERE ID = ".$input["ID"];
		return $sql;
	}
}
 
?>