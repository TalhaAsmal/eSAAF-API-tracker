<html>
    <head>
        <title>Remove battle</title>
    </head>

    <body>
<?php
    include("header.inc.php");
	connect_db();
    $battleID=$_GET['battleID'];
    
    $query="ALTER TABLE `soldier_roster` DROP `$battleID`";
    if(mysql_query($query)) 
        echo "Battle stats for battle $battleID successfully deleted.<br />";
    else {
        echo "Battle stats for battle $battleID could not be deleted.<br />";
        echo $query;
    }
    
    $query="DELETE FROM `battle_details` WHERE `BattleID`='$battleID'";
    if(mysql_query($query))
        echo "Battle details for $battleID sucessfully deleted. <br />";
    else {
        echo "Battle details for $battleID could not be deleed. <br />";
        echo $query;
    }
    
    mysql_close();
?>
        <br />
        <a href="index.htm">Return Home</a> | <a href="generatedamagereport.php">View, add, or update battle reports</a>
    </body>
</html>