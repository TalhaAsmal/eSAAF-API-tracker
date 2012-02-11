<?php
	$battleLogsFolder="/home/esaaf/public_html/battleLogs";
	$soldierPagesFolder="/home/esaaf/public_html/soldierPages";
	
	if(!is_dir($battleLogsFolder)) {
        mkdir($battleLogsFolder,0777);
    }
	
	if(!is_dir($soldierPagesFolder)) {
        mkdir($soldierPagesFolder,0777);
    }
	
    //Connects to the database
	function connect_db() {
		$mysql_host = "localhost";
		$mysql_database = "esaaf_damage";
		$mysql_user = "esaaf_primary";
		$mysql_password = "tdpgst";
		mysql_connect($mysql_host,$mysql_user,$mysql_password);
		@mysql_select_db($mysql_database) or die("Unable to select database");
		return true;
	}
?>