<?php
	$url="http://www.erepublik.com/en/country/economy/South-Africa";
	$page=file_get_contents($url);
	//var_dump($page);
	$regex="/all_accounts(.*?)<\/table>/sim";
	preg_match($regex,$page,$currencySection);
	
	//Get currency codes
	$regex="/\/>(.*?)</sim";
	preg_match_all($regex,$currencySection[0],$currencyCodes);
	
	//Get currency values
	$regex_whole="/<span class=\"special\">(.*?)<\/span>/";
	$regex_fractional="/<sup>(.*?)<\/sup>/";
	preg_match_all($regex_whole,$currencySection[0],$currencyWhole);
	preg_match_all($regex_fractional,$currencySection[0],$currencyFraction);
	
	foreach($currencyCodes[1] as $key => $code) {
		$currencyValue[trim($code)]=$currencyWhole[1][$key].$currencyFraction[1][$key];
	}
	
	date_default_timezone_set('America/Los_Angeles');
	echo "Timezone set to ".date_default_timezone_get()."<br />";
	
	include("../dbinfo.inc.php");
	$date=date('H:i');
	$eRepDay=floor((time()-strtotime("20-Nov-2007"))/86400);
	$date=$date." ".$eRepDay;
	
	$result=mysql_query("SHOW COLUMNS FROM `treasury_tracker` LIKE '$date'");
	if(mysql_num_rows($result)==0) {
		$query="ALTER TABLE `treasury_tracker` ADD `$date` DECIMAL(15,2) NOT NULL DEFAULT '0'";
		if(mysql_query($query))
			echo "Successfully added column for $date <br />";
		else
			echo "Could not add column for $date <br />";
	}
	else
		echo "Column for $date already exists <br />";
	
	foreach($currencyValue as $code => $value) {
		$result=mysql_query("SELECT * FROM `treasury_tracker` WHERE `Code`='$code'");
		if(mysql_num_rows($result)==0)
			mysql_query("INSERT INTO `treasury_tracker` (`Code`,`$date`) VALUES ('$code','$value')") or die("Unable to enter value of $value for $code on $date <br />");
		else
			mysql_query("UPDATE `treasury_tracker` SET `$date`='$value' WHERE `Code`='$code'") or die("Unable to update value of $value for $code on $date <br />");
	}
	
	mysql_close();
?>