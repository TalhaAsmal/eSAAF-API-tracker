<?php
    include('header.inc.php');
    $id=$_GET['id'];
	connect_db();
	
	$query="SELECT * FROM `soldier_roster` WHERE `Index`='$id'";
	$result=mysql_query($query);
	$name=mysql_result($result,0,"Name");
	
    $query="DELETE FROM `soldier_roster` WHERE `Index`='$id'";
    mysql_query($query);
	
	$query="DELETE FROM `soldier_strength` WHERE `Name`='$name'";
	mysql_query($query);
?>

<html>
    <head>
        <title>Delete soldier</title>
    </head>
    <body>
        <? echo "Soldier deleted. <br />";?>
        <a href="index.htm">Home</a>
    </body>
</html>