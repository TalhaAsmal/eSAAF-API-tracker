<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Baby Tracker</title>
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
	$citizenPage=simplexml_load_file("citizenPages/esa.xml");
	date_default_timezone_set('UTC');
?>
	<table class="sortable" cellspacing="2" border="1">
		<thead>
			<tr>
				<th>ID</th>
				<th>Age (days)</th>
				<th>Experience</th>
				<th>Profile Link</th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach($citizenPage->citizen as $citizen) {
		$id=$citizen->id;
		$tob=new DateTime($citizen->{"date-of-birth"});
		
		$experience=(int) $citizen->{"experience-points"};
		
		$currentDate=new DateTime("now");
		
		$interval=$tob->diff($currentDate);
		if($citizen->{"is-organization"}=="false") {
			echo "<tr>";
			echo "	<td>$id</td>
					<td>".$interval->format('%a')."</td>
					<td>$experience</td>
					<td><a href=\"http://www.erepublik.com/en/citizen/profile/$id\">Link</a>";
			echo "</tr>";
		}
	}
	
	echo " </tbody>
		</table>";
?>