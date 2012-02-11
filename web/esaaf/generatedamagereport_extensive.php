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
	if(!isset($_REQUEST['submit'])) {
?>
		<h1>Gather Damage Report</h1>
		<h2>Registered Battles</h2>
		
		<table border="2" cellspacing="2" cellpadding="2">
			<tr>
				<th>Battle ID</th>
				<th>Day</th>
				<th>Description</th>
				<th>Status</th>
				<th>View</th>
			</tr>
		<?php
			$done=0;
			connect_db();
			$table_name="battle_details";
			$query="SELECT * FROM $table_name ORDER BY BattleID";
			$result=mysql_query($query);
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				$read_battleID=mysql_result($result,$i,"BattleID");
				$read_description=mysql_result($result,$i,"Description");
				$read_battleDay=mysql_result($result,$i,"Day");
				$read_status=mysql_result($result,$i,"Status");
				echo "<tr>
						<td>$read_battleID</td>
						<td>$read_battleDay</td>
						<td>$read_description</td>
						<td>$read_status</td>
						<td><a href=\"generatedamagereport_extensive.php?battleID=$read_battleID&update=1&submit=1\">View</a></td>
					</tr>";
			}
			echo "</table>";
			
			mysql_close();
	}
	else {
		@set_time_limit(0);
		//Setup XML information
		$battleID=$_REQUEST['battleID'];
		$filename="battleLogs/$battleID/0.xml";
		$xml=simplexml_load_file($filename);
		$maxPages=0;
		$attacker=$xml->{"battle-info"}->attacker;
		$defender=$xml->{"battle-info"}->defender;
		
		//Gathers the battle statistics
		for($currentPage=0;$currentPage<=$maxPages;$currentPage++) {
			$filename="battleLogs/$battleID/".$currentPage.".xml";
			$xml = simplexml_load_file($filename);
			$maxPages=$xml->{"battle-info"}->{"max-pages"};
			
			while($done<=1) {
				foreach($xml->battles->battle as $fight){
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
				}
			}
		}
		
		echo "<h1>Battle Report for Battle $battleID</h1>";
		echo "<span class=\"style1\">Attacker: $attacker</span><br />";
		echo "<span class=\"style1\">Defender: $defender</span><br /><br />";
		
		foreach($damage as $country => $details) {
			foreach($details as $hour => $damage) {
				$hour1=$hour+1;
				echo "Country: $country <br />";
				echo "Damage: ".$damage."<br />";
				echo "Time Period: ".$hour.":00-".str_pad($hour1,2,"0",STR_PAD_LEFT).":00<br />";
				echo "<br /> <br />";
			}
		}
	}
?>
        <a href="index.htm">Return Home</a> | <a href="generatedamagereport.php">Add another battle</a>
    </body>
</html>