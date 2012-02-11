<?php
	include("dbinfo.inc.php");
	$currencyValues=simplexml_load_file("http://www.eustools.com/mm/mmERates.php/");
	$currencyBuyVolumes=simplexml_load_file("http://www.eustools.com/mm/mmEInstBVol.php/");
	$currencySellVolumes=simplexml_load_file("http://www.eustools.com/mm/mmEInstSVol.php/");
    
	foreach($currencyValues as $record) {
		$currency=$record->currency;
		$buyingRate=$record->buyprice;
		$sellingRate=$record->sellprice;
		$query="SELECT * FROM `currency_trading` WHERE `Currency`='$currency'";
		$result=mysql_query($query);
		if(mysql_num_rows($result)==0)
			$query="INSERT INTO `currency_trading` (`Currency`,`BuyingRate`,`SellingRate`) VALUES ('$currency','$buyingRate','$sellingRate')";
		else
			$query="UPDATE `currency_trading` SET `BuyingRate`='$buyingRate', `SellingRate`='$sellingRate' WHERE `Currency`='$currency'";
			
		mysql_query($query) or die(mysql_error());
	}
	
	foreach($currencyBuyVolumes as $record) {
		$currency=$record->currency;
		$trades=$record->trades;
		$volume=$record->volume;
		$query="UPDATE `currency_trading` SET `BuyVolume`='$volume', `BuyTransactions`='$trades' WHERE `Currency`='$currency'";
		mysql_query($query) or die(mysql_error());
	}
	
	foreach($currencySellVolumes as $record) {
		$currency=$record->currency;
		$trades=$record->trades;
		$volume=$record->volume;
		$query="UPDATE `currency_trading` SET `SellVolume`='$volume', `SellTransactions`='$trades' WHERE `Currency`='$currency'";
		mysql_query($query) or die(mysql_error());
	}

	$writer = new XMLWriter();

	$writer->openURI('/home/tasmal/public_html/currency.xml');
	$writer->startDocument('1.0');

	$writer->setIndent(4);

	$writer->startElement("records");

	$query="SELECT * FROM `currency_trading`";
	$result=mysql_query($query);
	$numRows=mysql_num_rows($result);
	$numFields=mysql_num_fields($result);
	
	for($i=0;$i<$numRows;$i++) {
		$writer->startElement("record"); //for each currency, start  record
		for($j=1;$j<$numFields;$j++) {
			$meta=mysql_fetch_field($result,$j);
			$fieldName=$meta->name;
			$value=mysql_result($result,$i,"$fieldName");
			$writer->writeElement($fieldName,$value); //write the data
		}
		$writer->endElement(); //end record
	}
	
	$writer->endElement(); //end records
	$writer->endDocument();
	$writer->flush();
	
	mysql_close();
	
	echo "Done!";
?>