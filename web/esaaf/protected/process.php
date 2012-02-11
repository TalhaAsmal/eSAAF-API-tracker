<html>
    <head>
        <title>Update Skills</title>
    </head>

    <body>
<?php
	include("lib_class.php");
	connect_db();
	
	//processes soldiers
	function process($id) {
		$currentSoldier=new soldier($id);
		switch (strtolower($_REQUEST['action'])) {
			case "enroll":
				switch (strtoupper($_REQUEST['branch'])) {
					case "PG" :
						$_REQUEST['branch'] = "Pretorian Guard";
						break;
					case "TD" :
						$_REQUEST['branch'] = "Training Division";
						break;
					case "PB" :
						$_REQUEST['branch'] = "Parabats";
						break;
				}
				
				$currentSoldier = new soldier($_REQUEST);
				$currentSoldier->grabSkills();
				$currentSoldier->updateDatabase();
				break; //enroll
			case "update":
				$currentSoldier->loadFromDatabase();
				$currentSoldier->grabSkills();
				$currentSoldier->updateDatabase();
				break; //update
				
			case "delete":
				$currentSoldier->deleteSoldier();
				break; //delete
				
			case "modify":
				echo "<h1>Modify soldier information</h1>";
				$currentSoldier->loadFromDatabase();
?>
				<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
					<input type="hidden" name="scope" value="soldier">
					<input type="hidden" name="action" value="enroll">
					<h2>eRepublik Profile ID:</h2> 
					<input type="hidden" name="id" value="<? echo $currentSoldier->id; ?>"> <? echo $currentSoldier->id; ?> <br />
					<br />
					
					<h2>eRepublik Name:</h2> 
					<? echo $currentSoldier->name; ?> <br />
					<br />
					<h2>eSAAF Rank:</h2>
					<input type="text" name="milRank" value="<? echo $currentSoldier->milRank; ?>"><br />
					<br />
					
					<h2>eSAAF Branch: </h2>
					<input type="text" name="branch" value="<? echo $currentSoldier->branch; ?>"><br />
					<br />
					
					<h2>eSAAF Battalion: </h2>
					<input type="text" name="battalion" value="<? echo $currentSoldier->battalion; ?>"><br />
					<br />
					
					<h2>eSAAF Platoon: </h2>
					<input type="text" name="platoon" value="<? echo $currentSoldier->platoon; ?>"><br />
					<br />
					
					<h2>Position Since: </h2>
					<input type="text" name="positionSince" value="<? echo $currentSoldier->positionSince; ?>"><br />
					<br />
					
					<h2>Email Address: </h2>
					<input type="text" name="emailAddress" value="<? echo $currentSoldier->emailAddress; ?>"><br />
					<br />
					
					<h2>Forum Registration: </h2>
					<input type="text" name="forum" value="<? echo $currentSoldier->forum; ?>"><br />
					<br />
					
					<input type="Submit" name="Submit" value="Update">
				</form>
<?				break;	//modify
		} //action
	}
	
    switch (strtolower($_REQUEST['scope'])) {
		case "all":
			$result=mysql_query("SELECT `Index` FROM `soldier_roster`") or die("Unable to select ID's<br />".mysql_error());
			while($row=mysql_fetch_assoc($result)) {
				process($row['Index']);
			} //for each row
			break; // case all
			
		case "platoon":
			//echo $_REQUEST['id'];
			$explodeBranch=explode("_",$_REQUEST['id']);
			$branchName=$explodeBranch[0];
			$explodeBattalion=explode("/",$explodeBranch[1]);
			$numBattalion=$explodeBattalion[0];
			$numPlatoon=$explodeBattalion[1];
			$platoon=new platoon($branchName, $numBattalion, $numPlatoon);
			foreach ($platoon->soldiers as $currentSoldier) {
				process($currentSoldier->id);
			}
			break; //platoon
			
		case "soldier":
			process($_REQUEST['id']);
			break; //soldier
	}
	
	@mysql_close();
?>
        <a href="index.html">Return Home</a> | <a href="enrollSoldier.htm">Enroll a soldier</a> | <a href="view.php">View registered soldiers</a>
    </body>
</html>