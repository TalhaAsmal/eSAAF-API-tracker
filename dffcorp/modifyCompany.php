<html>
    <head>
        <title>Modify Company Records</title>
    </head>

    <body>
<?php
	date_default_timezone_set('America/Los_Angeles');
	include("header.inc.php");
	if(!isset($_REQUEST['Submit'])) {
		echo "<h1> Existing Company Records </h1>";
		connect_db();
		$result=mysql_query("SELECT * FROM `companies`") or die("Unable to select existing companies <br />".mysql_error());
		$num=mysql_num_rows($result);
		if($num==0) {
			echo "No companies on record<br />";
		}
		else {
			echo "<table align=\"center\" border=\"2\" cellspacing=\"2\" cellpadding=\"2\">
					<tr>";
			while($field=mysql_fetch_field($result)) {
				echo "<th>$field->name</th>";
			}
			echo "	<th>Delete</th>
					<th>XML</th>
					<th>Update</th>
				</tr>";

			while($row=mysql_fetch_row($result)) {
				$id=$row[0];
				echo "<tr>";
				foreach($row as $value) {
					echo "<td>$value</td>";
				}
				echo '<td><a href="modifyCompany.php?Submit=1&id='.$id.'&action=delete">Delete</a></td>';
				echo '<td><a href="company_'.$id.'.xml">XML</a>';
				echo '<td><a href="modifyCompany.php?Submit=1&id='.$id.'&action=update">Update</a></td>';
				echo "</tr>";
			}

			echo "</table>";
			echo "<br />";
			
			echo '<a href="modifyCompany.php?Submit=1&action=update">Update all</a>';
			mysql_close();
		}
		
		echo "<h1>Add a new company</h1>";
		
		echo '<form action="'.$PHP_SELF.'" method="post">';
			echo 'Company ID: <input type="text" name="id" id="id"> <br />';
			echo '<input id="Submit" type="Submit" name="Submit" value="Submit" />';
		echo '</form>';
	}
	else {
		$id=$_REQUEST['id'];
		
		if($_REQUEST['action']=="delete") {
			deleteCompany($id);
			echo "Company $id deleted <br />";
		}
		elseif($_REQUEST['action']=="update") {
			if(isset($_REQUEST['id'])) {
				addCompany($id);
				echo "Company $id successfully updated in database<br />";
				flush();
				grabCompanyData($id);
				echo 'Company XML file can be found <a href="company_'.$id.'.xml">here</a><br />';
			}
			else {
				connect_db();
				$result=mysql_query("SELECT `ID` from `companies`") or die("Unable to grab existing companies<br />".mysql_error());
				while($row=mysql_fetch_row($result)) {
					$id=$row[0];
					addCompany($id);
					echo "Company $id successfully added to database<br />";
					flush();
					grabCompanyData($id);
					echo 'Company XML file can be found <a href="dffcorp/company_'.$id.'.xml">here</a><br />';
				}
				mysql_close();
			}
		}	
		else {
			addCompany($id);
			echo "Company $id successfully added to database<br />";
			flush();
			grabCompanyData($id);
			echo 'Company XML file can be found <a href="company_'.$id.'.xml">here</a>';
		}
	}
?>
    </body>
</html>