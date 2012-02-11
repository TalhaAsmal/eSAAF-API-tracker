<?php
include("header.inc.php");

	class soldier {
		var $name; //done
		var $id; //manual - done
		var $milRank; //manual - done
		var $emailAddress; //manual - done
		var $positionSince; //manual - done
		var $forum; //manual
		var $wellness; //done
		var $location; //done
		var $rankPoints; //done
		var $gameMil; //done
		var $strength; //done
		var $influence; //done
		var $branch; //manual - done
		var $battalion; //manual - done
		var $platoon; //manual - done
		var $citizenship; //done
		
		protected $rankLevel; //done
		protected $profile; //done - done
		
		private $query;
		private $result;
		private $row;
		
		private $militaryRanks=array("Recruit","Private","Corporal","Sergeant","Lieutenant","Captain","Major","Commander","Lt Colonel","Colonel","General","Field Marshal","Supreme Marshal","National Force","World Class Force","Legendary Force","God of War");
		
		function __construct($inputData) {
			if(is_array($inputData)){
				foreach ($inputData as $key => $value) {
					$this->$key=$value;
				}
			}
			else {
				$this->id=$inputData;
			}
		}
				
		function grabSkills() {
			while(!isset($this->profile)){$this->profile=simplexml_load_file("http://api.erepublik.com/v2/feeds/citizens/".$this->id);}
			
			$this->name=(string) $this->profile->name;
			$this->citizenship=(string) $this->profile->citizenship->country->name;
		
			$this->location=(string) $this->profile->residence->country->name;
			$this->wellness=(float) $this->profile->wellness;
						
			$this->rankPoints=(int) $this->profile->military->{"rank-points"};
			
			$this->gameMil=(string) $this->profile->military->rank;
			for($i=0;$i<(int) $this->profile->military->stars;$i++) {
				$this->gameMil=$this->gameMil."*";
			}
			
			$this->strength=(float) $this->profile->{"military-skills"}->{"military-skill"}->points;
			
			$this->rankLevel=array_search((string) $this->profile->military->rank, $this->militaryRanks);
			
			if($this->rankLevel>0) {$this->rankLevel=$this->rankLevel*4+((int) $this->profile->military->stars)-2;}
			//echo "Rank Level: $this->rankLevel <br />";
			
			$this->influence=$this->strength*((float) $this->rankLevel)/250.0+$this->strength/12.5+((float) $this->rankLevel)/2.5;
			unset($this->profile);
			return true;
		} //grabSkills

		function updateDatabase() {
			connect_db();
			if(get_magic_quotes_gpc()) { $this->name=mysql_real_escape_string(stripslashes($this->name));}
			else { $this->name=mysql_real_escape_string($this->name);}
			$this->query="INSERT INTO `soldier_roster` (`Index`, `Name`, `Email`, `Rank`, `Branch`, `Battalion`, `Platoon`, `Position Since`, `Country`, `Citizenship`, `Wellness`, `Game Military Rank`, `Strength`, `Influence`, `Rank Points`, `Forum`) VALUES ('$this->id', '$this->name', '$this->emailAddress', '$this->milRank', '$this->branch', '$this->battalion', '$this->platoon', '$this->positionSince', '$this->location', '$this->citizenship', '$this->wellness', '$this->gameMil', '$this->strength', '$this->influence', '$this->rankPoints', '$this->forum') ON DUPLICATE KEY UPDATE Name='$this->name', Email='$this->emailAddress', Rank='$this->milRank', `Branch`='$this->branch', `Battalion`='$this->battalion', `Platoon`='$this->platoon',`Position Since`='$this->positionSince', `Country`='$this->location', `Citizenship`='$this->citizenship', `Wellness`='$this->wellness', `Game Military Rank`='$this->gameMil', `Strength`='$this->strength', `Influence`='$this->influence', `Rank Points`='$this->rankPoints', Forum='$this->forum'";
			mysql_query($this->query) or die("Unable to update soldier roster<br />".mysql_error());
			echo "Database successfully updated with details for ".$this->milRank . " " . stripslashes($this->name) ."<br />";
			mysql_close();
			$this->exportXML();
		} //updateDatabase
		
		function deleteSoldier() {
			connect_db();
			$this->query="DELETE FROM `soldier_roster` WHERE `Index`='$this->id'";
			mysql_query($this->query) or die("Unable to delete soldier with Citizen ID $id<br />$query<br />".mysql_error());
			echo "Soldier deleted<br />";
			mysql_close();
			$this->exportXML();
			return true;
		}
		
		function loadFromDatabase() {
			connect_db();
			$this->query="SELECT `Index` as id, `Name` as name, `Email` as emailAddress, `Rank` as milRank, `Branch` as branch, `Battalion` as battalion, `Platoon` as platoon, `Position Since` as positionSince, `Country` as location, `Citizenship` as citizenship, `Wellness` as wellness, `Game Military Rank` as gameMil, `Strength` as strength, `Influence` as influence, `Rank Points` as rankPoints, `Forum` as forum FROM `soldier_roster` WHERE `Index`='$this->id'";
			$this->result=mysql_query($this->query) or die("Unable to SELECT table <br /> $this->query <br />".mysql_error());
			$this->row=mysql_fetch_assoc($this->result);
			$this->__construct($this->row);
			mysql_close();
			return true;
		}
		
		function exportXML() {
			$tempPlatoon=new platoon($this->branch, $this->battalion, $this->platoon);
			$tempPlatoon->exportXML();
		}
	} //soldier class

	class platoon {
	
		var $data;
		var $soldiers;
		
		private $xmlFileBase='/var/www/html/web/esaaf/xml';
		private $query;
		private $result;
		private $row;
		
		function __construct($branchName, $battalionNum, $platoonNum) {
			$this->data['id']=$platoonNum;
			$this->data['battalion']=$battalionNum;
			$this->data['branch']=$branchName;
			$this->query="SELECT `Index` as id, `Name` as name, `Email` as emailAddress, `Rank` as milRank, `Branch` as branch, `Battalion` as battalion, `Platoon` as platoon, `Position Since` as positionSince, `Country` as location, `Citizenship` as citizenship, `Wellness` as wellness, `Game Military Rank` as gameMil, `Strength` as strength, `Influence` as influence, `Rank Points` as rankPoints, `Forum` as forum FROM `soldier_roster` WHERE `Branch`='".$this->data['branch']."' AND `Battalion`='".$this->data['battalion']."' AND `Platoon`='".$this->data['id']."'";
			$this->loadPlatoon();
		}
		
		function loadPlatoon() {
			connect_db();
			$this->result=mysql_query($this->query) or die(mysql_error());
			$this->data['numSoldiers']=0;
			while($this->row=mysql_fetch_assoc($this->result)) {
				$this->soldiers[]=new soldier($this->row);
				$this->data['numSoldiers']++;
			}
		}
		
		function exportXML() {
			$writer=new XMLWriter();
			$xmlFile=$this->data['branch']."_".$this->data['battalion']."_".$this->data['id'].".xml";
			//global $host;
			
			
			if(!is_dir($this->xmlFileBase)) {
				mkdir($this->xmlFileBase,0777);
			}
			
			$writer->openURI($this->xmlFileBase."/".$xmlFile);
			$writer->startDocument('1.0');
			$writer->setIndent(4);
			$writer->startElement("platoon");
				$writer->startElement("info");
					foreach($this->data as $key => $value) {
						$writer->writeElement($key,$value);
					}
				$writer->endElement(); //info
				
				foreach ($this->soldiers as $currentSoldier){
					$writer->startElement("soldier");
						foreach($currentSoldier as $key => $value) {
							$writer->writeElement($key,$value);
						}
						//$writer->writeElement("delete",$host."process.php?scope=soldier&action=delete&id=".$currentSoldier->id);
						//$writer->writeElement("modify",$host."process.php?scope=soldier&action=modify&id=".$currentSoldier->id);
						//$writer->writeElement("update",$host."process.php?scope=soldier&action=update&id=".$currentSoldier->id);
					$writer->endElement(); // soldier
				}
			$writer->endElement(); // platoon
			$writer->endDocument();
			$writer->flush();
			
			echo "XML for ".$this->data['branch']."_".$this->data['battalion']."_".$this->data['id']." can be found <a href=\"xml/$xmlFile\">here</a><br />";
		}
	}
	
	class battalion {
		var $data;
		var $platoons;
		
		private $query;
		private $result;
		private $platoonNum;
				
		function __construct($branchName, $battalionNum) {
			$this->data['id']=$battalionNum;
			$this->data['branch']=$this->branchName;
			$this->query="SELECT DISTINCT Platoon FROM `soldier_roster` WHERE `Battalion`='$this->data[\'id\']' AND `Branch`='$this->data[\'branch\']";
		}
		
		function loadBattalion() {
			$this->result=mysql_query($this->query);
			$this->data['numPlatoons']=0;
			for($i=0;$i<mysql_num_rows($this->result);$i++) {
				$this->platoonNum=mysql_result($this->result,$i,"Platoon");
				$this->platoons[$platoonNum]=new platoon($this->data['branch'], $this->data['id'], $this->platoonNum);
				$this->platoons[$platoonNum]->loadPlatoon();
			}
		}
	}
?>