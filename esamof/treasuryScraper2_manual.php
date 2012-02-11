<?php
	if(!isset($_REQUEST['Submit'])) {
		echo '<form action="'.$PHP_SELF.'" method="POST">';
		echo '<textarea name="page" id="page" cols="40" rows="5">
				Enter your comments here...
				</textarea><br>';
		echo '<input id="Submit" type="Submit" name="Submit" value="Submit" />';
		echo '</form>';

	}
	else {
		if((int) date("j") > 5)
			$month=(string) date("F");
		else
			$month=(string) date("F",mktime(0,0,0,date("m")-1,date(d),date("Y")));
			
		/*$countryURL=array(	"South Africa" => "South-Africa", 
							"Australia" => "Australia", 
							"Bolivia" => "Bolivia",
							"China" => "China",
							"Israel" => "Israel",
							"Malaysia" => "Malaysia",
							"Philippines" => "Philippines",
							"Singapore" => "Singapore",
							"Thailand" => "Thailand");*/
		
		//foreach($countryURL as $country => $countryLink) {
			//$url="http://www.erepublik.com/en/country/economy/".$countryLink;
			//$page=file_get_contents($url);
			$countryLink="South-Africa";
			$page=$_REQUEST['page'];
			$regex="/all_accounts(.*?)<\/table>/sim";
			preg_match($regex,$page,$currencySection);
			
			//Get currency codes
			$regex="/\/>(.*?)</sim";
			preg_match_all($regex,$currencySection[0],$currencyCodes);
			
			//Get currency values
			$regex_whole="/<span class=\\\\\"special\\\\\">(.*?)<\/span>/sim";
			$regex_fractional="/<sup>(.*?)<\/sup>/s";
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
			
			$tableName='treasury_tracker_'.$month.'_'.$countryLink;
			
			mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
			  `Code` TEXT NOT NULL
			) ENGINE=MyISAM") or die("Could not add table<br />")	;

			$result=mysql_query("SHOW COLUMNS FROM `$tableName` LIKE '$date'");
			if(mysql_num_rows($result)==0) {
				$query="ALTER TABLE `$tableName` ADD `$date` DECIMAL(15,2) NOT NULL DEFAULT '0'";
				if(mysql_query($query))
					echo "Successfully added column for $country $date <br />";
				else
					echo "Could not add column for $country $date <br />";
			}
			else
				echo "Column for $country $date already exists <br />";
			
			foreach($currencyValue as $code => $value) {
				//mysql_query("INSERT INTO $tableName (`Code`,`$date`) VALUES ('$code','$value') ON DUPLICATE KEY UPDATE `$date`='$value'") or die("Error in INSERT/UPDATE query<br />".mysql_error());
				$result=mysql_query("SELECT * FROM `$tableName` WHERE `Code`='$code'");
				if(mysql_num_rows($result)==0)
					mysql_query("INSERT INTO `$tableName` (`Code`,`$date`) VALUES ('$code','$value')") or die("Unable to enter value of $value for $code on $date <br />");
				else
					mysql_query("UPDATE `$tableName` SET `$date`='$value' WHERE `Code`='$code'") or die("Unable to update value of $value for $code on $date <br />");
			}
			
		//}	
		mysql_close();
	}
?>