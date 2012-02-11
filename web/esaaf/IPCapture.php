<?php
	for($i=1;$i<=10;$i++) {
		$url[]="http://www.samair.ru/proxy/time-".str_pad($i,2,"0",STR_PAD_LEFT).".htm";
	}
	$url[]="http://www.proxy.org/tor.shtml";
	
	foreach($url as $key => $value) {
		$page=file_get_contents($value);
		$regex="/(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}/";
		preg_match_all($regex,$page,$IPs[$key]);
	}
	
	foreach($IPs as $value) {
		foreach($value[0] as $ip) {
			$IPList[]=$ip;
		}
	}
	
	$IPList=array_unique($IPList);
	
	foreach($IPList as $ip) {
		echo "$ip <br />";
	}
?>