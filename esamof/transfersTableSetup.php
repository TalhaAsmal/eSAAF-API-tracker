<?php
	include("dbinfo.inc.php");
	
	$countriesListURL="http://api.erepublik.com/v2/feeds/countries/";
	$countriesPage=simplexml_load_file($countriesListURL);
	
	$countries=array("South-Africa");
	$month=(string) date("F");
	
	connect_db();
	foreach($countries as $name) {
		$tableName='treasury_transfers_'.$month.'_'.$name;
		mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (`Code` TEXT NOT NULL) ENGINE=MyISAM") or die("Could not add table<br />".mysql_error());
		
		foreach($countriesPage->country as $countryData) {
			$data=(string) $countryData->currency;
			mysql_query("INSERT INTO `$tableName` (`Code`) VALUES ('$data')") or die (mysql_error());
			echo "$data done <br />";
		}
	}
	
	mysql_close();
?>