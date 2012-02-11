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
			connect_db();
			$query="SELECT * FROM `battle_details` ORDER BY BattleID";
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
						<td><a href=\"$PHP_SELF?battleID=$read_battleID&update=1&submit=1\">View</a></td>
					</tr>";
			}
			echo "</table>";
			
			mysql_close();
	}
	else {
		@set_time_limit(0);
		//Setup XML information
		$battleID=$_REQUEST['battleID'];
		$maxPages=0;
		
		//Gathers the battle statistics
		for($currentPage=0;$currentPage<=$maxPages;$currentPage++) {
			$filename="$battleLogsFolder/$battleID/".$currentPage.".xml";
			$xml = simplexml_load_file($filename);
			$attacker=$xml->{"battle-info"}->attacker;
			$defender=$xml->{"battle-info"}->defender;
			$maxPages=$xml->{"battle-info"}->{"max-pages"};
			
			foreach($xml->battles->battle as $fight){
				$citizen=$fight->citizen;
				$citizens[(string) $citizen]["ID"]=$fight->{"citizen-id"};
				$citizens[(string) $citizen]["Damage"]+=(int) $fight->damage;
				$explodedTime=explode(" ",$fight->time);
				$citizens[(string) $citizen]["Time"]=$fight->time;
				$citizens[(string) $citizen]["Fights"]++;
			}
		}
		
		echo "<h1>Battle Report for Battle $battleID</h1>";
		echo "<span class=\"style1\">Attacker: $attacker</span><br />";
		echo "<span class=\"style1\">Defender: $defender</span><br /><br />";
		
		foreach($citizens as $citizen => $details) {
			if($details["Damage"]>0) 
				$defenderDamage+=$details["Damage"];
			elseif($details["Damage"])
				$attackerDamage+=$details["Damage"];
			echo "Citizen Name: $citizen <br />";
			echo "Damage:".$details["Damage"]."<br />";
			echo "Fights: ".$details["Fights"]."<br />";
			//echo "Time:".$details["Time"]."<br />";
			echo "<br /> <br />";
		}
		
		echo "<h1> Final Tally</h1>";
		echo "Attacker Damage: $attackerDamage <br />";
		echo "Defender Damage: $defenderDamage <br />";
	}
?>
        <a href="index.htm">Return Home</a> | <a href="generatedamagereport.php">Add another battle</a>
    </body>
</html>