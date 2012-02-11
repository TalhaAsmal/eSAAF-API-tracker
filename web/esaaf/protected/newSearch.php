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
    </head>
    <body>
    <?php
		include('header.inc.php');
        if(!isset($_REQUEST['submit'])) { //If page hasn't been submitted, echo search form
			connect_db();
    ?>
        <h1>Search Damage Reports</h1>
        
        <form method="post" action="<?php echo $PHP_SELF;?>">
            
        <span class="style2">Search for a soldier</span><br />
        </b>
        <br />
            Soldier Name:
        <input name="SearchName" type="text" /><br />
        <br />
        <br />
        <span class="style2">Entire Branch</span><br />
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
        <span class="style2">Entire Battalion</span><br />
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
        <span class="style2">Entire Platoons</span><br />
        <?
            $query="SELECT DISTINCT `Branch`, `Battalion`, `Platoon` from `soldier_roster` ORDER BY `Branch`, `Battalion`, `Platoon`";
            $result=mysql_query($query) or die("Error in query: <br/> $query <br />".mysql_error());
            while($row=mysql_fetch_row($result)) {
                $branch=$row[0];
				$battalion=$row[1];			
				$platoon=$row[2];
                echo '<input name="depth" type="checkbox" value="'.$branch.'_'.$battalion.'/'.$platoon.'" />'.$branch.' '.$battalion.'/'.$platoon.'<br />';
            }
        ?>
        <br />
        <br />
        <input type="submit" value="Submit" name="submit" /><br />
        </form>
<? 
	}
    else {
		$searchName=$_REQUEST['SearchName'];
		$depth=$_REQUEST['depth'];
		$explodeBranch=explode("_",$depth);
		$branch=$explodeBranch[0];
		$explodeBattalion=explode("/",$explodeBranch[1]);
		$battalion=$explodeBattalion[0];
		$platoon=$explodeBattalion[1];
		
		$headers=array("Name","Platoon","Profile Link","eSAAF Rank","E-mail Address","Position Since","Forums","Wellness","Country","Attack Time","Mil Rank (Game)","Weapon","Wep Skill","Skill Name","Damage/Hit","Crit Hit %");
		$weaponSkills=array("","Greenhorn","Rookie","Hotshot","Marksman","Sharp Shooter","Professional","Expert","Ranger","Nemesis","Veteran","Veteran*");
		$basicDamage=array("Tank"=>14,"Rifle"=>10,"Air Unit"=>10,"Artillery"=>12);
		$critHitPercentages=array("Private"=>0.05,"Corporal"=>0.1,"Sergeant"=>0.2,"Lieutenant"=>0.3,"Captain"=>0.4,"Colonel"=>0.5,"General"=>0.6,"Field Marshal"=>0.7);
		
		if($branch!="Entire eSAAF") {
			$depthQuery="`Branch`='$branch'";
			if($battalion!='') {
				$depthQuery=$depthQuery." AND `Battalion`='$battalion'";
				
				if(isset($platoon))
					$depthQuery=$depthQuery." AND `Platoon`='$platoon'";
			}
		}
		else 
			$depthQuery=1;
		
		$nameQuery="`Name` LIKE '%$searchName%'"; //Search by name
		connect_db();
		$query="SELECT * FROM `soldier_roster` LEFT JOIN `military_skills` USING(`Index`) LEFT JOIN `worker_skills` USING(`Index`) WHERE $nameQuery AND $depthQuery ORDER BY `Branch`,`Battalion`,`Platoon`,`Name`";
		echo '<table align="center" border="1" cellspacing="2" cellpadding="2">
				<tr>';
		foreach($headers as $header) {
			echo "<th>$header</th>";
		}
		echo "</tr>";

		$result=mysql_query($query) or die("Unable to select roster.<br /> $query<br />".mysql_error());

		while($row=mysql_fetch_assoc($result)) {
			$name=$row['Name'];
			$profileLink='<a href="http://www.erepublik.com/en/citizen/profile/'.$row['Index'].'" target="_blank">http://www.erepublik.com/en/citizen/profile/'.$row['Index'].'</a>';
			$platoon=$row['Branch']." ".$row['Battalion']."/".$row['Platoon'];
			$eSAAFrank=$row['Rank'];
			$email=$row['Email'];
			$positionSince=$row['Position Since'];
			$forums=$row['Forums'];
			$wellness=$row['Wellness'];
			$country=$row['Country'];
			$attackTime=$row['Attack Time'];
			
			$milRankName=$row['Military Rank'];
			$milRankNameStripped=trim($milRankName,"*");
			$milRankStars=strlen($milRankName)-strlen($milRankNameStripped);
			
			$weapons=array("Tank"=>$row['Tank'],"Rifle"=>$row['Rifle'],"Artillery"=>$row['Artillery'],"Air Unit"=>$row['Air Unit']);
			$weaponSkill=max($weapons);
			if($weaponSkill>10) {$weaponSkillStripped=10;}
			else {$weaponSkillStripped=$weaponSkill;}
			$weaponStars=$weaponSkill-$weaponSkillStripped;
			$weapon=array_keys($weapons,$weaponSkill);
			$weaponSkillName=$weaponSkills[$weaponSkillStripped];
			for($i=0;$i<$weaponStars;$i++) {$weaponSkillName.="*";}
			
			$damagePerHit=(1+($weaponSkill-1)/10.0)*$basicDamage[$weapon[0]];
			$critHitChance=$critHitPercentages[$milRankNameStripped]+0.01*$stars;
						
			echo "<tr>";
			echo "<td>$name</td>";
			echo "<td>$platoon</td>";
			echo "<td>$profileLink</td>";
			echo "<td>$eSAAFrank</td>";
			echo "<td>$email</td>";
			echo "<td>$positionSince</td>";
			echo "<td>$forums</td>";
			echo "<td>$wellness</td>";
			echo "<td>$country</td>";
			echo "<td>$attackTime</td>";
			echo "<td>$milRankName</td>";
			echo "<td>".$weapon[0]."</td>";
			echo "<td>$weaponSkill</td>";
			echo "<td>$weaponSkillName</td>";
			echo "<td>$damagePerHit</td>";
			echo "<td>$critHitChance</td>";
			echo "</tr>";
		}
		
		echo "</table>";
    }
    mysql_close();
?>
		<br />
		<a href="index.htm">Home</a>
    </body>
</html>