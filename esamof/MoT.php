<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Country Taxation</title>
		<style type="text/css">
            .style1
            {
                font-weight: bold;
                font-size: x-large;
            }
        </style>
	</head>
	
	<body>
<?php
	set_time_limit(0);
	
	$countriesPage="http://api.erepublik.com/v2/feeds/countries";
	$countriesList=simplexml_load_file($countriesPage);
	
	foreach($countriesList->country as $country) {
		$countryNames[]=str_replace(" ","-",$country->name);
		$countryID[str_replace(" ","-",(string) $country->name)]=$country->id;
		$citizens[str_replace(" ","-",(string) $country->name)]=$country->{"citizen-count"};
	}
	
	unset($country);
	
	sort($countryNames,SORT_STRING);
	
	foreach($countryNames as $country) {
		$countryURL="http://www.erepublik.com/en/country/economy/".$country;
		$countryPage=file_get_contents($countryURL);
		
		$taxesTableRegex="/<table class=\"citizens largepadded\">(\s*.*?)<\/table>/sim";
		preg_match($taxesTableRegex,$countryPage,$taxesTable);
		
		$allTaxesRegex="/<img title=\"(.*?)<\/tr>/sim";
		preg_match_all($allTaxesRegex,$taxesTable[0],$itemInfo);
		
		foreach($itemInfo[0] as $currentItem) {
			$itemsRegex="/alt=\"(.*?)\"/";
			preg_match($itemsRegex,$currentItem,$item);
			$taxesRegex="/<span class=\"special\">(\s*.*?)<\/span>/";
			preg_match_all($taxesRegex,$currentItem,$tax);
			$allTaxes[$item[1]][0] = (int) $tax[1][0];
			$allTaxes[$item[1]][1] = (int) $tax[1][1];
			if(is_numeric($tax[1][2]))
				$allTaxes[$item[1]][2] = (int) $tax[1][2];
			else
				$allTaxes[$item[1]][2] = 0;
		}
		
		echo "<h1>$country</h1>";
		echo '<span class="style1">Citizens: '.$citizens[$country].'</span><br />
				<br />
				<br />';
		echo '<table cellspacing="2" cellpadding="2" border="2">
			<tr>
				<th>Item</th>
				<th>Income Tax</th>
				<th>Import Tax</th>
				<th>VAT</th>
			</tr>';
			
		foreach($allTaxes as $product => $taxes) {
			echo "<tr>
					<td>$product</td>
					<td>$taxes[0]%</td>
					<td>$taxes[1]%</td>
					<td>$taxes[2]%</td>
				</tr>";
		}
		
		echo "</table>";
		
		flush();
	}
?>
	</body>
</html>