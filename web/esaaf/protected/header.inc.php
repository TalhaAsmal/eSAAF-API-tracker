<?php
	/*
	$battleLogsFolder="/home/esaaf/public_html/battleLogs";
	$soldierPagesFolder="/home/esaaf/public_html/soldierPages";
	
	if(!is_dir($battleLogsFolder)) {
        mkdir($battleLogsFolder,0777);
    }
	
	if(!is_dir($soldierPagesFolder)) {
        mkdir($soldierPagesFolder,0777);
    }
	*/
	$host="";
    date_default_timezone_set('America/Los_Angeles');
	//Connects to the database
	function connect_db() {
		$mysql_host = "localhost";
		$mysql_database = "esaaf";
		$mysql_user = "esaaf";
		$mysql_password = "esaaf12357";
		mysql_connect($mysql_host,$mysql_user,$mysql_password);
		@mysql_select_db($mysql_database) or die("Unable to select database");
		return true;
	}
?>