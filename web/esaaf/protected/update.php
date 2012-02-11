<?php
	include("lib_class.php");
	connect_db();
	
	$result=mysql_query("SELECT `Index` FROM `soldier_roster`") or die("Unable to select ID's<br />".mysql_error());
	while($row=mysql_fetch_assoc($result)) {
		$currentSoldier=new soldier($row['Index']);
		$currentSoldier->loadFromDatabase();
		$currentSoldier->grabSkills();
		$currentSoldier->updateDatabase();
	}
	
	@mysql_close();
?>