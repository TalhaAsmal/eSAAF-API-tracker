<?php
	//echo "Working... <br />";
	
	include("lib_class.php");
	
	switch (strtoupper($_POST['branch'])) {
		case "PG" :
			$_POST['branch'] = "Pretorian Guard";
			break;
		case "TD" :
			$_POST['branch'] = "Training Division";
			break;
		case "PB" :
			$_POST['branch'] = "Parabats";
			break;
	}
	
	$currentSoldier = new soldier($_POST);
	$currentSoldier->grabSkills();
	$currentSoldier->updateDatabase();
	$tempPlatoon=new platoon($currentSoldier->branch, $currentSoldier->battalion, $currentSoldier->platoon);
	$tempPlatoon->exportXML();
?>