<html>
    <head>
        <title>Strength Tracker</title>
    </head>

    <body>
<?php
	include("header.inc.php");
	//Adds column for today if it doesn't exist
	connect_db();
	$query="SELECT * FROM `soldier_strength` LIMIT 1";
	$result=mysql_query($query);
	$date=date('F j Y');
	$dateExists=0;
	
    for($i=0;$i<mysql_num_fields($result);$i++)
    {
        $meta=mysql_fetch_field($result,$i);
        if($meta->name == $date)
            $dateExists=1;
    }
	
    if($dateExists==0) {
        $query="ALTER TABLE `soldier_strength` ADD `$date` DECIMAL(10,3) NOT NULL DEFAULT '0'";    
        if(mysql_query($query))
            echo "Sucessfully added column for $date <br />";
        else
            echo "Could not add column for $date <br />";
    }
	else
		echo "Column for $date already exists <br />";
	
	//Gathers strength for each soldier and adds it to database table    
	$query="SELECT * FROM `soldier_roster`";
	$result=mysql_query($query);
	$num=mysql_num_rows($result);
	mysql_close();

    for($i=0;$i<$num;$i++) {
		$name=mysql_result($result,$i,"Name");
		$filename="http://api.erepublik.com/v1/feeds/citizens/".rawurlencode($name)."?by_username=true";
		$xml=@simplexml_load_file($filename);
        $fp=fopen("$soldierPagesFolder/$name.xml",'w+');
		if($xml)
			fwrite($fp,$xml->asxml());
        fclose($fp);
        echo "$name done <br />";
    }
	
	connect_db();
	
	$query="SELECT * FROM `soldier_roster`";
	$result=mysql_query($query);
	$num=mysql_num_rows($result);
	for($i=0;$i<$num;$i++) {
		$name=mysql_result($result,$i,"Name");
		$squad=mysql_result($result,$i,"Squad");
		$company=mysql_result($result,$i,"Company");
		$rank=mysql_result($result,$i,"Rank");
		$division=mysql_result($result,$i,"Division");
		
		$filename="$soldierPagesFolder/$name.xml";
		$xml=@simplexml_load_file($filename);
		$strength=$xml->strength;
		
		$result_exists=mysql_query("SELECT * FROM `soldier_strength` WHERE `Name`='$name'");
		if(mysql_num_rows($result_exists)==0) {
			$insert_query="INSERT INTO `soldier_strength` (`Name`,`Rank`,`Division`,`Company`,`Squad`,`$date`) VALUES ('$name','$rank','$division','$company','$squad','$strength')";
			mysql_query($insert_query) or die("Unable to enter strength for new soldier $name on $date");
		}
		elseif(mysql_result($result_exists,0,"$date")==0) {
			$update_query="UPDATE `soldier_strength` SET `Rank`='$rank', `Division`='$division', `Company`='$company', `Squad`='$squad', `$date`='$strength' WHERE `Name`='$name'";
			mysql_query($update_query) or die("Unable to enter strength for existing soldier $name on $date <br />".$update_query);
		}
	}
	
	mysql_close();
?>