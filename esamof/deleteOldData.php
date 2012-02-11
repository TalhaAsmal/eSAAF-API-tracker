<?php	
	include("../dbinfo.inc.php");
	
	$result=mysql_query("SELECT * FROM `treasury_tracker_feb`");
	
	$start=0;
	
	for($i=0;$i<mysql_num_fields($result);$i++) {
		$field=mysql_fetch_field($result);
		$header=$field->name;
		$fieldNames[$i]=$header;
		if($header=="00:13 809")
			$stopColumn=$i;
	}
	
	for($i=2;$i<$stopColumn;$i++) {
		$fieldName=$fieldNames[$i];
		$query="ALTER TABLE `treasury_tracker_feb` DROP `$fieldName`";
		mysql_query($query) or die(mysql_error());
	}
	
	mysql_close();
?>