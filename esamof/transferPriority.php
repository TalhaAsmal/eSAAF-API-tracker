<html>
	<head>
		<title>Transfer Priority</title>
	</head>
	<body>
	<h1> Transfer Priority List</h1>
	Please remember to leave at least 2500 ZAR in the treasury at all times
		
<?php
	include("dbinfo.inc.php");
	
	$country="South-Africa";
	

		if((int) date("j") > 5)
			$month=(string) date("F");
		else
			$month=(string) date("F",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
	
	connect_db();
	$query="SELECT * FROM `treasury_tracker_".$month."_".$country."`";
	$result=mysql_query($query) or die(mysql_error());
	$rows=mysql_num_rows($result);
	$lastField=mysql_fetch_field($result,mysql_num_fields($result)-1)->name;
	
	for($i=0;$i<mysql_num_rows($result);$i++) {
			$code=mysql_result($result,$i,"Code");
			$value=mysql_result($result,$i,$lastField);
			$currencies[$code]=$value;
	}
	
	arsort($currencies);
	
	echo '<table cellspacing="2" cellpadding="2" border="2">
		<tr>
			<th>Code</th>
			<th>Amount</th>
		</tr>';
		
		foreach($currencies as $key=>$value) {
			echo '<tr>';
			echo '<td>'.$key.'</td>';
			echo '<td>'.$value.'</td>';
			echo '</tr>';
		}
		echo '</table>';
?>