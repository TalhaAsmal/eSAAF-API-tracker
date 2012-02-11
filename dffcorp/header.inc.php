<?php
	date_default_timezone_set('America/Los_Angeles');
	//checked
	function connect_db() {
		/*$mysql_host = "mysql11.000webhost.com";
		$mysql_database = "a9991062_company";
		$mysql_user = "a9991062_dffcorp";
		$mysql_password = "GiftsAreBad123";
		*/
		$mysql_host = "localhost";
		$mysql_database = "dffcorp";
		$mysql_user = "dffcorp";
		$mysql_password = "dffcorp12357";
		
		while(!(mysql_connect($mysql_host,$mysql_user,$mysql_password)))
		{
			mysql_connect($mysql_host,$mysql_user,$mysql_password);
		}
		@mysql_select_db($mysql_database) or die("Unable to select database");
	}
	
	//checked
	function addCompany($id) {
		while(!isset($profile) || !$profile) { @$profile=simplexml_load_file("http://api.erepublik.com/v2/feeds/companies/$id");}
		$name=$profile->name;
		unset($profile);
		
		connect_db();
		$query="INSERT INTO `companies` (`ID`,`Name`) VALUES ('$id','$name') ON DUPLICATE KEY UPDATE `Name`='$name'";
		mysql_query($query) or die("Unable to update database with $name<br />$query<br/>".mysql_error());
		mysql_close();
		
		return true;
	}
	
	//checked
	function deleteCompany($id) {
		connect_db();
		mysql_query("DELETE FROM `companies` WHERE `ID`='$id'") or die("Unable to delete $id from database<br />$query<br/>".mysql_error());
		mysql_close();
		
		return true;
	}
	
	//checked
	function grabCompanyData($id) {
	//Grab population data
		while(!isset($countries) || !$countries){@$countries=simplexml_load_file("http://api.erepublik.com/v2/feeds/countries");}
		echo "Countries feed loaded<br />";
		
		foreach($countries->country as $country) {
			$population[(int) $country->id]=(int) $country->{"citizen-count"};
		}
	//Grab all the data from the company feed
		while(!isset($profile) || !$profile) { @$profile=simplexml_load_file("http://api.erepublik.com/v2/feeds/companies/$id");}
		echo "Company API page loaded <br />";
		flush();
		$customizationPoints=0;
		
		foreach($profile->{"export-licenses"}->{"export-license"} as $exportLicense) {
			$license[(int) $exportLicense->country->id]['name']=$exportLicense->country->name;
			$license[(int) $exportLicense->country->id]['status']=(string) $exportLicense->{"is-active"};
		}
		
		$customizationLevel=$profile->{"customization-level"};
		$country['name']=(string) $profile->country->name;
		$country['id']=(string) $profile->country->id;
		
		foreach($profile->customization->children() as $key=>$value) {
			$customization[(string) $key]=(string) $value;
			$customizationPoints+=$value;
		}
		
		$industry['name']=$profile->industry->name;
		$industry['id']=(int) $profile->industry->id;
		
		$companyName=(string) $profile->name;
		
		foreach($profile->employees->employee as $tempEmployee) {
			$profileID=(int) $tempEmployee->id;
			$employee[$profileID]["name"]=(string) $tempEmployee->name;
			while(!isset($employeeProfile) || !$employeeProfile) { @$employeeProfile=simplexml_load_file("http://api.erepublik.com/v2/feeds/citizens/$profileID");}
			echo "API page for employee ".$employee[$profileID]["name"]." loaded<br />";
			flush();
			$employee[$profileID]["wellness"]=$employeeProfile->wellness;
			$unsortedSkill[$profileID]=(int) $employeeProfile->{"work-skill-points"};
			if($unsortedSkill[$profileID]>=0 && $unsortedSkill[$profileID]<20)
				$employee[$profileID]["skill-level"]=1;
			elseif($unsortedSkill[$profileID]>=20 && $unsortedSkill[$profileID]<100)
				$employee[$profileID]["skill-level"]=2;
			elseif($unsortedSkill[$profileID]>=100 && $unsortedSkill[$profileID]<500)
				$employee[$profileID]["skill-level"]=3;
			elseif($unsortedSkill[$profileID]>=500 && $unsortedSkill[$profileID]<2000)
				$employee[$profileID]["skill-level"]=4;
			elseif($unsortedSkill[$profileID]>=2000 && $unsortedSkill[$profileID]<5000)
				$employee[$profileID]["skill-level"]=5;
			elseif($unsortedSkill[$profileID]>=5000)
				$employee[$profileID]["skill-level"]=6+floor(log10($unsortedSkill[$profileID]/5000)/log10(2));
				
			unset($employeeProfile);
		}

		if(isset($unsortedSkill)) {arsort($unsortedSkill);}
		
		$rmStock=$profile->{"raw-materials-in-stock"};
		$stock=$profile->stock;
		
		unset($profile);
		
		//Grab market offers
		foreach($license as $key=>$value) {
			$countryName=(string) $value['name'];
			if($value['status']==="true" && $population[$key]>0) {
				$marketURL="http://api.erepublik.com/v2/feeds/market/".$industry['id']."/$key";
				if($customizationPoints>0) {
					for($i=1;$i<=($customizationPoints/$customizationLevel-10)/10;$i++) {
						$marketURL=$marketURL."/0";
					}
				}
				
				while(!isset($marketFeed) || !$marketFeed) { @$marketFeed=simplexml_load_file($marketURL);}
				echo "Market feed for $countryName loaded<br />";
				flush();
				if(isset($marketFeed->offer)){
					foreach($marketFeed->offer as $offer) {
						$price=((float) $offer->price)*100;
						if(isset($offers[$countryName][$price])) { $offers[$countryName][$price]+=$offer->amount;}
						else {$offers[$countryName][$price]=$offer->amount;}
					}
				}
				unset($marketFeed);
			}
		}
		
		//Build XML
		$filename='/var/www/html/dffcorp/company_'.$id.'.xml';
		$writer = new XMLWriter();

		$writer->openURI($filename);
		$writer->startDocument('1.0');
		$writer->setIndent(4);

		$writer->startElement("company");
			$writer->writeElement("company-name",$companyName);
			$writer->writeElement("country",$country['name']);
			$writer->writeElement("industry",$industry['name']);
			$writer->writeElement("customization-level",$customizationLevel);
			$writer->writeElement("customization-points",$customizationPoints);
			
			$writer->startElement("customization");
				if($customizationPoints>0) {
					foreach($customization as $key=>$value) {
						$writer->writeElement($key,$value);
					}
				}
			$writer->endElement();
			
			$writer->startElement("export-licenses");
				foreach($offers as $countryOffer=>$value) {
					$i=0;
					foreach($value as $price=>$amount) {
						if($i<2) {
							$writer->startElement("offer");
								$writer->writeElement("country",$countryOffer);
								$writer->writeElement("price",$price/100.0);
								$writer->writeElement("amount",$amount);
							$writer->endElement();
							$i++;
						}
					}
				}
			$writer->endElement();
			
			$writer->startElement("employees");
				if(isset($employee)) {
					foreach($unsortedSkill as $profileID => $value) {
						$writer->startElement("employee");
							$writer->writeElement("citizen-id",$profileID);
							foreach($employee[$profileID] as $key=>$value) {
								$writer->writeElement($key,$value);
							}
						$writer->endElement();
					}
				}
			$writer->endElement();
			
			$writer->writeElement("rm-stock",$rmStock);
			$writer->writeElement("stock",$stock);
		$writer->endElement();
		$writer->endDocument();
		$writer->flush();
		
		return true;
	}
?>