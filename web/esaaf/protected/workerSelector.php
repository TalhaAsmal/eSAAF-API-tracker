<html>
    <head>
        <title>eSAAF Worker Selector</title>
    </head>

    <body>
		<h1> eSAAF Potential Worker Identifier </h1>
<?php
    include("header.inc.php");
	if(!isset($_REQUEST['Submit'])) 
	{
		connect_db();
		
		echo '<form action="'.$PHP_SELF.'" method="post">';
		
		echo '<h2>Please select the branch of interest:</h2>';
		$result=mysql_query("SELECT DISTINCT Branch AS Branch FROM `soldier_roster`") or die("Unable to extract branches<br />".mysql_error());
		$num=mysql_num_rows($result);
		for($i=0;$i<$num;$i++)
		{
			$branch=mysql_result($result,$i,"Branch");
			echo '<input type="radio" name="Branch" value="'.$branch.'" />'.$branch.'<br />';
		}
		
		echo '<input type="radio" name="Branch" value="All" /> All <br />';
		
		echo '<h2>Please select the skill of interest:</h2>';
		$result=mysql_query("SHOW COLUMNS FROM `soldier_roster`") or die("Unable to extract column names<br />".mysql_error());
		$num=mysql_num_rows($result);
		for($i=6;$i<$num;$i++)
		{
			$skill=mysql_result($result,$i,"Field");
			echo '<input type="radio" name="Skill" value="'.$skill.'" />'.$skill.'<br />';
		}
		
		echo '<input id="Submit" type="Submit" name="Submit" value="Submit" />';
		echo '</form>';
	}
	else
	{
		$branch=$_REQUEST['Branch'];
		$skill=$_REQUEST['Skill'];

		connect_db();
		if($branch=="All")
			$query="SELECT * FROM `soldier_roster` ORDER BY `$skill` DESC";
		else 
			$query="SELECT * FROM `soldier_roster` WHERE `Branch`='$branch' ORDER BY `$skill` DESC";
		
		$result=mysql_query($query) or die("Unable to SELECT table <br /> $query <br />".mysql_error());

		echo "<table align=\"center\" border=\"2\" cellspacing=\"2\" cellpadding=\"2\">
				<tr>";
		while($field=mysql_fetch_field($result))
		{
			echo "<th>$field->name</th>";
		}
		echo "	<th>Update</th>
				<th>Delete</th>
			</tr>";

		while($row=mysql_fetch_row($result))
		{
			$id=$row[0];
			echo "<tr>";
			foreach($row as $value)
			{
				echo "<td>$value</td>";
			}
			echo '<td><a href="modifySoldier.php?id='.$id.'&action=update">Update</a></td>';
			echo '<td><a href="modifySoldier.php?id='.$id.'&action=delete">Delete</a></td>';
			echo "</tr>";
		}
		
		echo "</table>";
		
	}
	@mysql_close();
?>
        <a href="index.htm">Return Home</a> | <a href="enrollSoldier.htm">Enroll another soldier</a> | <a href="viewSoldiers.php">View registered soldiers</a>
    </body>
</html>