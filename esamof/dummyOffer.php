<?php
	$countries=simplexml_load_file("http://api.erepublik.com/v2/feeds/countries");
	foreach($countries->country as $country) {
			$id=(string) $country->id;
			echo '<a href="http://www.erepublik.com/en/exchange/create?account_type=citizen#buy_currencies=62;sell_currencies='.$id.';">'.$id.'</a><br />';
		}
?>