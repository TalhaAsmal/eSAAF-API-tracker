<?php
	include("header.inc.php");
	connect_db();
	$query="SELECT * FROM `battle_details`";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
	
	for($i=0;$i<$rows;$i++) {
		$battleID=mysql_result($result,$i,"BattleID");
		$folder="$battleLogsFolder/$battleID";
		
		if(!is_dir($folder)) {
			mkdir($folder,0777);
		}
		//Loads all XML battle logs for current battle
		$maxPages=0;
		for($currentPage=0;$currentPage<=$maxPages;$currentPage++) {
			$filename="http://api.erepublik.com/v1/feeds/battle_logs/$battleID/$currentPage/";
			$xml = simplexml_load_file($filename);
			$maxPages=$xml->{"battle-info"}->{"max-pages"};
			if(!file_put_contents("$folder/$currentPage.xml",$xml->asxml()))
				echo "Page $currentPage for battle $battleID NOT done <br />";
			flush();
		}
		
		echo "Battle $i out of $rows done <br />";
	}
?>
