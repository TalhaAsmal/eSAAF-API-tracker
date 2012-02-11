<?php
	date_default_timezone_set('UTC');
	function grabFeed($currency1,$currency2) {
		while(!isset($feed) || !$feed){ @$feed=simplexml_load_file("http://api.erepublik.com/v2/feeds/exchange/".$currency1."/".$currency2);}
		$i=1;
		while($i<10) {
			$offer=$feed->offer[$i];
			if((string) $offer->seller['nil'] != 'true') {
				$offer=(float) $offer->{"exchange-rate"};
				unset($feed);
				return $offer;
			}
			$i++;
		}
	}
	
	//echo grabFeed("GOLD","ITL");
	
	echo date('Y/m/d H:i:s e P T')
?>