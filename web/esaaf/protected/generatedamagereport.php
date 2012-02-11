<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Battlestats Generation Status</title>
    </head>

    <body>
<?php
	include('header.inc.php');
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
				<th>Update</th>
				<th>Delete</th>
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
						<td><a href=\"generatedamagereport.php?battleID=$read_battleID&update=1&submit=1\">Update</a></td>
						<td><a href=\"deleteBattle.php?battleID=$read_battleID\">Delete</a></td>
					</tr>";
			}
			
			mysql_close();
		?>
		</table>
		<h2>Add or Update Battle</h2>
		<p>To update battle details, enter Battle ID and the new value of the detail you 
			would like to change</p>
		
		<form method="post" action="generatedamagereport.php">
		
		Battle ID:
		<br />
		<input name="battleID" type="text" /><br />
		<br />
		Day:
		<br />
		<input name="day" type="text" /><br />
		<br />
		Description:<br />
		<textarea name="description" rows="5" cols="30"></textarea>
		<br />
		<input id="update" type="hidden" name="update" value="0"/>
		<br />
		<input type="submit" value="Submit" name="submit" /></form>
<?php
	}
	else {
		@set_time_limit(0);
		//Setup XML information
		$battleID=$_REQUEST['battleID'];
		$battleDescription=$_REQUEST['description'];
		$update=$_REQUEST['update'];
		$battleDay=$_REQUEST['day'];
		$maxPages=0;
		$battleExists=0;
		$folder="$battleLogsFolder/$battleID";
		
		if(!is_dir($folder)) {
			mkdir($folder,0777);
		}
		
		//Loads all XML battle logs for current battle
		for($currentPage=0;$currentPage<=$maxPages;$currentPage++) {
			$filename="http://api.erepublik.com/v1/feeds/battle_logs/$battleID/$currentPage/";
			$xml = simplexml_load_file($filename);
			$maxPages=$xml->{"battle-info"}->{"max-pages"};
			$isActive=$xml->{"battle-info"}->{"is-active"};
			$attacker=$xml->{"battle-info"}->attacker;
			$defender=$xml->{"battle-info"}->defender;
			$pages[$currentPage]=$xml;
			if(file_put_contents("$folder/$currentPage.xml",$xml->asxml()))
				echo "Page $currentPage done <br />";
			flush();
		}

		//Connects to DB
		connect_db();
		//If adding a new battle (Update==0), or updating details, update the battle information table
		if($update!=1) {
			$query="SELECT * FROM `battle_details` WHERE `BattleID`='$battleID'";
			$result=mysql_query($query);
			$num=mysql_num_rows($result);
			if($num==0) { //New Battle
				$query="INSERT INTO `battle_details` (`BattleID`,`Day`,`Description`) VALUES ('$battleID','$battleDay','$battleDescription')";
				if(mysql_query($query))
					echo "Battle details added<br />";
				else {
					echo "Battle details not added <br /> $quey <br />";
				}
			}
			else { //Updating description
				if($battleDescription!='') {
					$query="UPDATE `battle_details` SET `Description`='$battleDescription' WHERE BattleID='$battleID'";
					if(mysql_query($query))
						echo "Description changed<br />";
					else {
						echo "Description not changed<br />";
						echo "$query <br />";
					}
				}
				if($battleDay!='') { //Updating day
					$query="UPDATE `battle_details` SET `Day`='$battleDay' WHERE BattleID='$battleID'";
					if(mysql_query($query))
						echo "Day changed<br />";
					else {
						echo "Day not changed<br />";
						echo "$query <br />";
					}
				}
			}
		}
		
		if($isActive=="1")
			mysql_query("UPDATE `battle_details` SET `Status`='In progress' WHERE BattleID='$battleID'");
		elseif($isActive=="0")
			mysql_query("UPDATE `battle_details` SET `Status`='Completed' WHERE BattleID='$battleID'");
			
		//Load database table
		$query="SELECT * FROM `soldier_roster` LIMIT 1";
		$result=mysql_query($query);
		
		//Checks if a column for the current battle exists
		for($i=0;$i<mysql_num_fields($result);$i++)
		{
			$meta=mysql_fetch_field($result,$i);
			if($meta->name == $battleID)
				$battleExists=1;
		}
		
		//Add column for current battle if it doesn't exist
		if($battleExists==0) {
			$query="ALTER TABLE `soldier_roster` ADD `$battleID` INT NOT NULL DEFAULT '0'";    
			if(mysql_query($query))
				echo "Sucessfully added column for battle $battleID <br />";
			else
				echo "Could not add column for battle $battleID <br />";
		}
		
		//Gathers the battle stats
		$result=mysql_query("SELECT * FROM `soldier_roster`");
		$num=mysql_num_rows($result);
		for($i=0;$i<$num;$i++) {
			$damage=0;
			$soldier=mysql_result($result,$i,"Name");
			$rank=mysql_result($result,$i,"Rank");
			$id=mysql_result($result,$i,"Index");
			
			foreach($pages as $page => $xml) {
				foreach($xml->battles->battle as $fight){
					if($fight->citizen == $soldier){
						$damage=$damage+ABS($fight->damage);
					}
				}
				$timeParts=explode(' ',microtime());
				$endTime=$timeParts[1].substr($timeParts[0],1);
			}
			
			$query="UPDATE `soldier_roster` SET `$battleID`='$damage' WHERE `Index`='$id'";        
			if(mysql_query($query))
				echo "$damage damage done by $rank $soldier added to database <br />";
			else
			{
				echo "$damage damage done by $rank $soldier could not be added to soldier roster <br />";
				echo "$query <br />";
			}
		}
				
		mysql_close();
	}
?>
        <a href="index.htm">Return Home</a> | <a href="generatedamagereport.php">Add another battle</a>
    </body>
</html>