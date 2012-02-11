<html>
	<head>
		<title>Voter Registration</title>
	</head>
	<body>
<?php
	$regionFile="http://api.erepublik.com/v1/feeds/regions/504";
	$regionXML=simplexml_load_file($regionFile);
	
	echo "Processing...<br />";
	flush();
	$i=0;
	foreach($regionXML->citizens->citizen as $citizen) {
		$url="http://api.erepublik.com/v1/feeds/citizens/".$citizen->id;
		$file=simplexml_load_file($url);
		if((int) $file->wellness >=25 && $file->citizenship->country=="South Africa") {
			//$citizens[]=$file->name;
			echo $file->name."<br />";
			flush();
			$i++;
		}
	}
	
	echo "<br /> Total: $i <br />";
?>	
	</body>
</html>