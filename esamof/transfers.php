<html>
	<head>
		<title>Transfer Tracker</title>
	</head>
	<body>
		<h1> Transfer Tracker </h1>

<?php
	include("dbinfo.inc.php");
	if(!isset($_REQUEST['Submit'])) {
?>
		<form method="POST" action="">
			<h2>Currency</h2>
			<input type="Text" name="Currency" /> <br /> 
			<h2>Amount</h2>
			<input type="Text" name="Amount" /> <br />
			<h2>Day</h2>
			<input type="Text" name="Day" /> <br />
			<input type="Submit" name="Submit" value="Submit" />
		</form>
<?php
	}
	else {
		$currency=$_REQUEST['Currency'];
		$amount=$_REQUEST['Amount'];
		$day=$_REQUEST['Day'];
		
		date_default_timezone_set('America/Los_Angeles');
		if((int) date("j") > 6)
			$month=(string) date("F");
		else
			$month=(string) date("F",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
			
		$country="South-Africa";
				
		$tableName='treasury_transfers_'.$month.'_'.$country;
		connect_db();
		
		mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
		  `Code` TEXT NOT NULL
		) ENGINE=MyISAM") or die("Could not add table<br />");
		
		$result=mysql_query("SHOW COLUMNS FROM `$tableName` LIKE '$day'");
		
		if(mysql_num_rows($result)==0) {
			$query="ALTER TABLE `$tableName` ADD `$day` DECIMAL(15,2) NOT NULL DEFAULT '0'";
			if(mysql_query($query))
				echo "Successfully added column for $country $day <br />";
			else
				echo "Could not add column for $country $day <br />";
		}
		else
			echo "Column for $country $day already exists <br />";
			
		$result=mysql_query("SELECT * FROM `$tableName` WHERE `Code`='$currency'");
		if(mysql_num_rows($result)==0)
			mysql_query("INSERT INTO `$tableName` (`Code`,`$day`) VALUES ('$currency','$amount')") or die("Unable to enter value of $amount for $currency on $day <br />");
		else
			mysql_query("UPDATE `$tableName` SET `$day`= '$amount' WHERE `Code`='$currency'") or die("Error in UPDATE query<br />".mysql_error());
	
		echo "<a href=\"\">Add another</a><br />";
		
		mysql_close();
	}
		
?>