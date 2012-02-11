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
	function dumpData($data,$file) {
		//Build XML
		$writer = new XMLWriter();
		$writer->openURI($file);
		$writer->startDocument('1.0');
			$writer->setIndent(4);
			$writer->startElement("exchanges");
				$writer->writeElement("updated",date('Y/m/d H:i:s')); //2008/08/07 18:11:31
				foreach($data as $countryName=>$info) {
					$writer->startElement("country");
						$writer->writeElement("Code",$countryName);
						foreach($info as $key=>$value) {
							$writer->writeElement($key,$value);
						}
					$writer->endElement(); //Country
				}
		$writer->endDocument();
		$writer->flush();
		
		return true;
	}
	
	function refreshData() {
		while(!isset($countries) || !$countries){$countries=simplexml_load_file("http://api.erepublik.com/v2/feeds/countries");}
		echo "Countries feed loaded<br />";
		
		$filename='/var/www/html/dffcorp/currencies.xml';
		$filename2='/var/www/html/esamof/currencies.xml';
		
		foreach($countries->country as $country) {
			$regions=0;
			foreach($country->regions->region as $region) { $regions++; }
			$countryName=(string) $country->name;
			$code=(string) $country->currency;
			$population=(int) $country->{"citizen-count"};
			$exchangeRates[$code]['Name']=$countryName;
			$exchangeRates[$code]['Population']=$population;
			$exchangeRates[$code]['Regions']=$regions;
			$exchangeRates[$code]['ToGold']=grabFeed($code,"GOLD");
			$exchangeRates[$code]['FromGold']=grabFeed("GOLD",$code);
			$exchangeRates[$code]['Yield']=(string) round((($exchangeRates[$code]['ToGold']*$exchangeRates[$code]['FromGold'])-1.00)*100.00,2) . "%";
			ksort($exchangeRates,SORT_STRING);
			echo "$countryName done<br />";
			flush();
		}
		
		unset($countries);
		echo "Countries unset<br />";
		dumpData($exchangeRates,$filename);
		dumpData($exchangeRates,$filename2);
		echo 'XML file written. (<a href="currencies.xml">View</a>)';
		
		return true;
	}
	
	refreshData();
?>