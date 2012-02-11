<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Tracker Output</title>
        <style type="text/css">
            .style2
            {
                font-weight: bold;
                font-size: x-large;
            }
        </style>
		<script src="sorttable.js"></script>
    </head>
    <body>
    <?php
        if(!isset($_REQUEST['submit'])) { //If page hasn't been submitted, echo search form
			include('header.inc.php');
			connect_db();
    ?>
        <h1>Search</h1>
        
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php /*            
        <span class="style2">Entire Branch (Not implemented yet)</span><br />
        <?
            $query="SELECT DISTINCT `Branch` from `soldier_roster` ORDER BY `Branch`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            while($row=mysql_fetch_row($result)) {
                $branch=$row[0];
                echo '<input name="depth" type="radio" value="'.$branch.'" />'.$branch.' <br />';
            }
        ?>
            <input name="depth" type="radio" value="Entire eSAAF" />Entire eSAAF<br />
        <br />
        <br />
        <span class="style2">Entire Battalion (Not implemented yet)</span><br />
        <?
            $query="SELECT DISTINCT `Branch`, `Battalion` from `soldier_roster` ORDER BY `Branch`, `Battalion`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            while($row=mysql_fetch_row($result)) {
                $branch=$row[0];
				$battalion=$row[1];
                echo '<input name="depth" type="radio" value="'.$branch.'_'.$battalion.'" />'.$branch.' '.$battalion.'<br />';
            }
        ?>
        <br />
        <br />
*/?>
        <span class="style2">Entire Platoon</span><br />
        <?
            $query="SELECT DISTINCT `Branch`, `Battalion`, `Platoon` from `soldier_roster` ORDER BY `Branch`, `Battalion`, `Platoon`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            while($row=mysql_fetch_row($result)) {
                $branch=$row[0];
				$battalion=$row[1];			
				$platoon=$row[2];
                echo '<input name="depth[]" type="checkbox" value="'.$branch.'_'.$battalion.'/'.$platoon.'" />'.$branch.' '.$battalion.'/'.$platoon.'<br />';
            }
			
			mysql_close();
        ?>
        <br />
        <br />
        <input type="submit" value="Submit" name="submit" /><br />
        </form>
<? 
	}
    else {
		include('lib_class.php');
		$depth=$_REQUEST['depth'];
		
		foreach($depth as $value) {
			$explodeBranch=explode("_",$value);
			$splitBranch=explode(" ",$explodeBranch[0]);
			$branchName=$explodeBranch[0];
			$explodeBattalion=explode("/",$explodeBranch[1]);
			$numBattalion=$explodeBattalion[0];
			$numPlatoon=$explodeBattalion[1];
			
			if(isset($numPlatoon)) {
				$platoon=new platoon($branchName, $numBattalion, $numPlatoon);
				echo "<h2>".$branchName." ".$numBattalion."/".$numPlatoon." (".$platoon->data['numSoldiers']." soldiers) </h2>";
				echo '<a href="'.$host.'process.php?scope=platoon&action=modify&id='.$value.'">Modify</a> | <a href="'.$host.'process.php?scope=platoon&action=delete&id='.$value.'">Delete</a> | <a href="'.$host.'process.php?scope=platoon&action=update&id='.$value.'">Update</a><br />';
				echo '<table class="sortable" border="1" cellspacing="2">';
					echo "<thead>";
						echo "<tr>";
						foreach(array_keys(get_object_vars($platoon->soldiers[0])) as $value) {
							echo "<th>$value</th>";
						}
						echo "<th>Modify</th>
								<th>Delete</th>
								<th>Update</th>";
						echo "</tr>";
					echo "</thead>";
					echo "<tbody>";
						foreach($platoon->soldiers as $currentSoldier) {
							echo "<tr>";
							foreach($currentSoldier as $value) {
								echo "<td>$value</td>";
							}
							echo "<td><a href=".$host."process.php?scope=soldier&action=modify&id=".$currentSoldier->id.">Modify</a></td>
									<td><a href=".$host."process.php?scope=soldier&action=delete&id=".$currentSoldier->id.">Delete</a></td>
									<td><a href=".$host."process.php?scope=soldier&action=update&id=".$currentSoldier->id.">Update</a></td>";
							echo "</tr>";
						}
					echo "</tbody>";
				echo "</table>";
			}
		}
    }
?>
		<br />
		<a href="index.html">Home</a>
    </body>
</html>