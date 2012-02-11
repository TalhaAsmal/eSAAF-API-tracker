<?php
    include("header.inc.php");
	connect_db();
    $query="SELECT * FROM `soldier_roster` LIMIT 1";
    $result=mysql_query($query);
    for($field=6;$field<mysql_num_fields($result);$field++) {
        $exists=0;
        $meta=mysql_fetch_field($result,$field);
        $battleID=$meta->name;
        $query_get="SELECT * FROM `battle_details` WHERE `BattleID`='$battleID'";
        $result_get=mysql_query($query_get);
        if(mysql_num_rows($result_get)==0) {
            $query_add="INSERT INTO `battle_details` (`BattleID`) VALUES ('$battleID')";
            mysql_query($query_add) or die(mysql_error());
        }
    }
?>