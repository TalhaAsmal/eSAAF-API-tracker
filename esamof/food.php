<?php
	include("dbinfo.inc.php");
	$query="SELECT * FROM `countries`";
	$result=mysql_query($query);
	$i=0;
	$health=$_REQUEST['health'];
	$happiness=$_REQUEST['happiness'];
	while($i<mysql_num_rows($result)) {
		$id=mysql_result($result,$i,"ID");
		$code=(string) mysql_result($result,$i,"Code");
		$country=(string) mysql_result($result,$i,"Country");
		$URL="http://api.erepublik.com/v2/feeds/market/1/$id/$happiness/$health";
		$page=simplexml_load_file($URL);
		$currencyPage=simplexml_load_file("http://api.erepublik.com/v2/feeds/exchange/$code/GOLD");
		$price=(double) $page->offer->price;
		$conversion=(double) $currencyPage->offer->{"exchange-rate"};
		$price=$price*$conversion;
		$amount=(int) $page->offer->amount;
		$prices[$country]=$price;
		$amounts[$country]=$amount;
		echo "$country done<br />";
		flush();
		$i++;
	}
	mysql_close();
	
	asort($prices);
	
	echo '<table cellspacing="2" cellpadding="2" border="2">
			<tr>
				<th>Country</th>
				<th>Price (Gold)</th>
				<th>Amount</th>
			</tr>';
			
	foreach($prices as $country => $value) {
		echo "<tr>
				<td>$country</td>
				<td>$value</td>
				<td>$amounts[$country]</td>
			</tr>";
	}
	
	echo "</table>";
	
?>