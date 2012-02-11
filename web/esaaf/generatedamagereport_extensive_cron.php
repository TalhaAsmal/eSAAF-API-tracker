<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Battlestats Generation Status</title>
		<style type="text/css">
            .style1
            {
                font-weight: bold;
                font-size: x-large;
            }
        </style>

    </head>
    <body>
<?php
	include("header.inc.php");
		@set_time_limit(0);
		//Setup XML information
		$battleID=$_SERVER["argv"][1];
		$maxPages=0;
		$hourArray=(array) null;

		//Gathers the battle statistics
		for($currentPage=0;$currentPage<=$maxPages;$currentPage++) {
			$filename=$battleLogsFolder."/$battleID/".$currentPage.".xml";
			$xml = simplexml_load_file($filename);
			$attacker=$xml->{"battle-info"}->attacker;
			$defender=$xml->{"battle-info"}->defender;
			$maxPages=$xml->{"battle-info"}->{"max-pages"};
			
			
				foreach($xml->battles->battle as $fight){
					//if($done<=50) {
						$citizenID=(int) $fight->{"citizen-id"};
						if(!($citizenship[$citizenID])) {
							$citizenPage=simplexml_load_file("http://api.erepublik.com/v1/feeds/citizens/$citizenID");
							$currentCitizenship=(string) $citizenPage->citizenship->country;
							$citizenship[$citizenID]=$currentCitizenship;
							$done++;
						}
						else {
							$currentCitizenship=$citizenship[$citizenID];
						}
						$explodedTime=explode(" ",$fight->time);
						$explodedHour=explode(":",$explodedTime[1]);
						$hour=$explodedHour[0];
						$damage[$currentCitizenship][$hour]+=$fight->damage;
						if(!in_array($hour,$hourArray)) {
							$hourArray[]=$hour;
						}
					//}
				}
		}
		sort($hourArray);
		
		echo "<h1>Battle Report for Battle $battleID</h1>";
		echo "<span class=\"style1\">Attacker: $attacker</span><br />";
		echo "<span class=\"style1\">Defender: $defender</span><br /><br />";
		//Table Headers
		echo '<table cellspacing="2" cellpadding="2" border="2">
					<tr>
						<th>Country</th>';
		foreach($hourArray as $currentHour) {
			$currentHour1=$currentHour+1;
			echo "<th>".str_pad($currentHour,2,"0",STR_PAD_LEFT).":00-".str_pad($currentHour1,2,"0",STR_PAD_LEFT).":00</th>";
		}
		echo "<th>Total Damage</th>";
		echo "</tr>";
		
		foreach($damage as $country => $details) {
			$totalDamage=0;
			echo "<tr>
					<td>$country</td>";
			foreach($hourArray as $currentHour) {
				echo "<td>$details[$currentHour]</td>";
				$totalDamage+=$details[$currentHour];
			}
			echo "<td>$totalDamage</td>";
			echo "</tr>";
		}
		echo "</table>";
?>
    </body>
</html>