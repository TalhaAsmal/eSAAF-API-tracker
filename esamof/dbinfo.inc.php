<?php
	date_default_timezone_set('America/Los_Angeles');
	
    function connect_db() {
		$mysql_host = "localhost";
		$mysql_database = "esamof";
		$mysql_user = "esamof";
		$mysql_password = "esamof12357";
		mysql_connect($mysql_host,$mysql_user,$mysql_password);
		@mysql_select_db($mysql_database) or die("Unable to select database");
		return true;
	}
?>