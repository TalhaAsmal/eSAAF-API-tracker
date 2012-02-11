<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Strength Search</title>
        <style type="text/css">
            .style2
            {
                font-weight: bold;
                font-size: x-large;
            }
        </style>
    </head>
    <body>
    <?php
		include('header.inc.php');
        $searchName=$_REQUEST['SearchName'];
        $searchDivisions=$_REQUEST['SearchDivisions'];
		$searchCompanies=$_REQUEST['SearchCompanies'];
        $searchSquads=$_REQUEST['SearchSquads'];
        $searchDays=$_REQUEST['SearchDays']; //Day isolation is critical
        $searchBreakdown=$_REQUEST['SearchBreakdown'];
        $allSquads=$_REQUEST['AllSquads'];
        $allDivisions=$_REQUEST['AllDivisions'];
        $allDays=$_REQUEST['AllDays'];
		$allCompanies=$_REQUEST['AllCompanies'];
        
        connect_db();
		
		function output($query,$breakdown,$numNotDates) {
			$result_data=mysql_query($query) or die("Unable to perform $breakdown search<br />$query<br />".mysql_error());
			echo "<table align=\"center\" border=\"2\" cellspacing=\"2\" cellpadding=\"2\">
					<tr>";
			for($i=0;$i<mysql_num_fields($result_data)-1;$i++) {
				$meta=mysql_fetch_field($result_data,$i);
				$heading=$meta->name;
				echo "<th>$heading</th>";
			}
			echo "</tr>";
			
			//Get Data
			for ($i=0;$i<mysql_num_rows($result_data);$i++) {
				echo "<tr>";
				$totalSoldiers=mysql_result($result_data,$i,"TotalSoldiers");
				for($field=0;$field<mysql_num_fields($result_data)-1;$field++) { //Get data values
					$meta=mysql_fetch_field($result_data,$field);
					$name=$meta->name;
					if($field>$numNotDates-1)
						$value=round(mysql_result($result_data,$i,"$name")/$totalSoldiers,3);
					else
						$value=mysql_result($result_data,$i,"$name");
					echo "<td>$value</td>";
				}		
				echo "</tr>";
			}
			
			echo "</table>";
		}

        if(!isset($_REQUEST['submit'])) { //If page hasn't been submitted, echo search form
    ?>
        <h1>Search Strength Reports</h1>
        
        <form method="post" action="<?php echo $PHP_SELF;?>">
            
        <span class="style2">Search for a soldier</span><br />
        <br />
            Soldier Name:
        <input name="SearchName" type="text" /><br />
        <br />
        <br />
        <span class="style2">Select Division</span><br />
        <?
            $query="SELECT DISTINCT `Division` from `soldier_strength` ORDER BY `Division`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            $num=mysql_num_rows($result);
            for($i=0;$i<$num;$i++) {
                $division=mysql_result($result,$i,"Division");
                echo "<input name=\"SearchDivisions[]\" type=\"checkbox\" value=\"$division\" />$division <br />";
            }
        ?>
            <input name="AllDivisions" type="checkbox" value="1" checked="checked" />All<br />
        <br />
        <br />
        <span class="style2">Select Companies</span><br />
        <?
            $query="SELECT DISTINCT `Company` from `soldier_strength` ORDER BY `Company`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            $num=mysql_num_rows($result);
            for($i=1;$i<$num;$i++) { //Start at 1 to ignore CoS
                $company=mysql_result($result,$i,"Company");
                echo "<input name=\"SearchCompanies[]\" type=\"checkbox\" value=\"$company\" />Company $company <br />";
            }
        ?>
            <input name="AllCompanies" type="checkbox" value="1" checked="checked" />All<br />
        <br />
        <br />
        <span class="style2">Select Squads</span><br />
        <?
            $query="SELECT DISTINCT `Squad` from `soldier_strength` ORDER BY `Squad`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            $num=mysql_num_rows($result);
            for($i=1;$i<$num;$i++) { //Start at 1 to ignore CoS and company CO
                $squad=mysql_result($result,$i,"Squad");
                echo "<input name=\"SearchSquads[]\" type=\"checkbox\" value=\"$squad\" />Squad $squad <br />";
            }
        ?>
            <input name="AllSquads" type="checkbox" value="1" checked="checked" />All<br />
        <br />
        <br />
        <span class="style2">Select Days</span><br />
		<table>
			<tr>
			<?
				$query="SELECT * from `soldier_strength` LIMIT 1";
				$result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
				$j=0;
				for($i=6;$i<mysql_num_fields($result);$i++) {
					$day=mysql_fetch_field($result,$i)->name;
					echo "<td><input name=\"SearchDays[]\" type=\"checkbox\" value=\"$day\" />$day </td>\n";
					$j++;
					if($j==5) {
						echo "
						</tr>
						<tr>";
						$j=0;
					}
				}
			?>
				<td><input name="AllDays" type="checkbox" value="1" checked="checked" />All<br /></td>
			</tr>
		</table>
        <br />
        <br />
        <span class="style2">Report Breakdown</span><br class="style3" />
        Choose what breakdown you would like to see<br />
        <input name="SearchBreakdown[]" type="checkbox" value="soldiers"/>Individual Soldiers<br />
        <input name="SearchBreakdown[]" type="checkbox" value="squads" />Squads<br />
        <input name="SearchBreakdown[]" type="checkbox" value="companies" />Companies<br />
        <input name="SearchBreakdown[]" type="checkbox" value="divisions" />Divisions<br />
        <input name="SearchBreakdown[]" type="checkbox" value="overall" />Overall eSAAF<br />
        <br />
        <input type="submit" value="Submit" name="submit" /><br />
            
        </form>
<? 
	mysql_close();
    }
    else {
	//SELECT `Name`,`Rank`,`Division`,`Company`,`Squad`, DAYS WHERE {Soldier} AND {
	
		$nameQuery="`Name` LIKE '%$searchName%'"; //Search by name

		if(!$allDivisions) {
			foreach($searchDivisions as $division) {//Search for divisions
				$divisionQuery=$divisionQuery."`Division`='$division' OR ";
			}
			$divisionQuery=$divisionQuery."0";
		}
		else {
			$divisionQuery="1";
		}
		
		if(!$allCompanies) {
			foreach($searchCompanies as $company) {
				$companyQuery=$companyQuery."`Company`='$company' OR ";
			}
			$companyQuery=$companyQuery."0";
		}
		else {
			$companyQuery="1";
		}
		
		if(!$allSquads) {
			$squadQuery=implode(", `Squad`='",$searchSquads);
			$squadQuery="`Squad`='".$squadQuery."'";
		}
		else {
			$squadQuery="1";
		}
		
		//Begin select applicable day
		if($allDays) {
			$query_get_days="SELECT * FROM `soldier_strength` LIMIT 1";
			$result_get_days=mysql_query($query_get_days);
			$numFields=mysql_num_fields($result_get_days);
			for($i=6;$i<$numFields;$i++) {
				$day=mysql_fetch_field($result_get_days,$i)->name;
				$days[$i-6]="SUM(`$day`) AS `$day`";
			}		
		}
		else {
			foreach($searchDays as $key =>$day) {
				$days[$key]="SUM(`$day`) AS `$day`";
			}
		}
		$dayQuery=implode(", ",$days);
		//End day selection
		
		$searchQuery=$dayQuery.", COUNT(`Company`) AS `TotalSoldiers` FROM `soldier_strength` WHERE ($nameQuery) AND ($divisionQuery) AND ($companyQuery) AND ($squadQuery)";
		
		foreach($searchBreakdown as $key=>$breakdown) {
			switch($breakdown) {
				case "soldiers":
					$currentSearchQuery="SELECT `Name`,`Rank`,`Division`,`Company`,`Squad`, ".$searchQuery." GROUP BY `Division`,`Company`,`Squad`,`Rank`,`Name` ORDER BY `Division`,`Company`,`Squad`,`Rank`,`Name`";
					echo "<h1> Individual Soldiers </h1>";
					$numNotDates=5;
					break;
				case "squads":
					$currentSearchQuery="SELECT `Division`,`Company`,`Squad`, ".$searchQuery." GROUP BY `Division`,`Company`,`Squad` ORDER BY `Division`, `Company`,`Squad`";
					echo "<h1> Squads </h1>";
					$numNotDates=3;
					break;
				case "companies":
					$currentSearchQuery="SELECT `Division`,`Company`, ".$searchQuery." GROUP BY `Division`,`Company` ORDER BY `Division`,`Company`";
					echo "<h1> Companies </h1>";
					$numNotDates=2;
					break;
				case "divisions":
					$currentSearchQuery="SELECT `Division`, ".$searchQuery." GROUP BY `Division` ORDER BY `Division`";
					echo "<h1> Divisions </h1>";
					$numNotDates=1;
					break;
				case "overall":
					$currentSearchQuery="SELECT ".$searchQuery;
					echo "<h1> Overall eSAAF </h1>";
					$numNotDates=0;
					break;
			}
			output($currentSearchQuery,$breakdown,$numNotDates);
		}
    }
    mysql_close();
?>
		<br />
		<a href="index.htm">Home</a>
    </body>
</html>