<html>
    <head>
        <title>eSAAF Mass Enrollment</title>
    </head>

    <body>
<?php   
	if(!isset($_REQUEST['submit'])) {
?>
		<form action="<?php $PHP_SELF?>" method="post" enctype="multipart/form-data">
			<label for="file">Filename:</label>
			<input type="file" name="file" id="file" />
			<br />
			<input type="submit" name="submit" value="Submit" />
		</form>
<?php
	}
	else {
		include("header.inc.php"); 
		include("lib_class.php");
	
		if($_FILES["file"]["error"] > 0) {
			echo "Error:".$_FILES["file"]["error"].">br />";
		}
		else {
			move_uploaded_file($_FILES["file"]["tmp_name"],"upload/".$_FILES["file"]["name"]);
			
			echo "Stored in: upload/".$_FILES["file"]["name"]."<br />";
			$fileLocation="upload/".$_FILES["file"]["name"];
			
			$soldierList=fopen($fileLocation,"r") or exit("Unable to open file");
			$data['branch']=trim(fgets($soldierList));
			while(!feof($soldierList)) {
				$data['battalion']=trim(fgets($soldierList));
				$data['platoon']=trim(fgets($soldierList));
				$numSoldiers=trim(fgets($soldierList));
				for($i=0;$i<$numSoldiers;$i++) {
					$exists=0;
					$tmp=explode(",",trim(fgets($soldierList)));
					$data['id']=trim($tmp[0]);
					$data['milRank']=trim($tmp[1]);
					$currentSoldier=new soldier($data);
					$currentSoldier->grabSkills();
					$currentSoldier->updateDatabase();
				}
				
				$currentPlatoon=new platoon($data['branch'], $data['battalion'], $data['platoon']);
				$currentPlatoon->exportXML();
			}
		}
	}
	@fclose($soldierList);
?>
        <a href="index.htm">Return Home</a> | <a href="enrollSoldier.htm">Enroll another soldier</a> | <a href="viewSoldiers.php">View registered soldiers</a>
    </body>
</html>