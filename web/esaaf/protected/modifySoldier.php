<html>
    <head>
        <title>Modify soldier information</title>
    </head>
    
    <body>
<?php
	include("header.inc.php");
	
	$action=$_REQUEST['action'];
    $id=$_REQUEST['id'];
	
	if ($action=="delete") {
		echo "<h1>Delete soldier information</h1>";
		deleteSoldier($id);
		echo "Soldier with Citizen ID $id has been deleted";
	}
	elseif ($action=="update") {
		echo "<h1>Modify soldier information</h1>";
	
		connect_db();
		$query="SELECT * FROM `soldier_roster` WHERE `Index`='$id'";
		$result=mysql_query($query);
		$num=mysql_num_rows($result);
		
		while($row=mysql_fetch_assoc($result)) {
?>
			<form method="POST" action="enrollSoldier.php">
				Citizen ID: <input type="hidden" name="ID" value="<? echo $row['Index']; ?>"> <? echo $row['Index']; ?> <br /> <br />
				eRepublik Name: <? echo $row['Name']; ?> <br /> <br />
				Military Branch: <br />
				<input type="text" name="Branch" value="<? echo $row['Branch']; ?>"><br /><br />
				Rank in the eSAAF:<br />
				<input type="text" name="Rank" value="<? echo $row['Rank']; ?>"><br /><br />
				Military Battalion: <br />
				<input type="text" name="Battalion" value="<? echo $row['Battalion']; ?>"><br /><br />
				Position Since: <br />
				<input type="text" name="Position" value="<? echo $row['Position Since']; ?>"><br /><br />
				Attack Time: <br />
				<input type="text" name="Attack" value="<? echo $row['Attack Time']; ?>"><br /><br />
				Email Address: <br />
				<input type="text" name="Email" value="<? echo $row['Email']; ?>"><br /><br />
				Forum Registration: <br />
				<input type="text" name="Forum" value="<? echo $row['Forum']; ?>"><br /><br />
				<input type="Submit" name="Submit" value="Update">
			</form>
<?		}
		mysql_close();
	}	
?>
    </body>
</html>