<?php
	$filename='citizens.gzip';
	$url='http://api.erepublik.com/v2/feeds/countries/51/citizens.xml.gz';
	$compressed=file_get_contents($url);
	$uncompressed=gzdecode($compressed);
	
	$file=simplexml_load_string($uncompressed);
?>