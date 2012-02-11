<?php
	if(!isset($_REQUEST['submit'])) {
?>
		<form action="<?php $PHP_SELF?>" method="post" enctype="multipart/form-data">
			<input type="radio" name="file" value="small" /> Small <br />
			<input type="radio" name="file" value="large" /> Small <br />
			<br />
			<input type="submit" name="submit" value="Submit" />
		</form>
<?php
	}
	else {
		//Select appropriate input file
		if($_REQUEST['file']=="small")
			$inputFile=fopen('botTrust_small.txt','r');
		else if ($_REQUEST['file']=="large")
			$inputFile=fopen('botTrust_large.txt','r');
			
		$testCases=(int) trim(fgets($inputFile));
		for($i=1;$i<=$testCases;$i++) {
			$sequence=trim(fgets($inputFile));
			$timeTaken=problemA($sequence);
			echo "Case #$i: $timeTaken <br />";
		}
		
		fclose($inputFile);
	}
	
	function problemA($line) {
	
		$exploded=explode($line);
		$numberOfButtons=$exploded[0];
		for($j=1;$j<=2*$numberOfButtons;$j=$j) {
			$buttons[$j]["robot"]=$exploded[2*$j-1];
			$buttons[$j]["button"]=$exploded[2*$j];
		}
		
		
			$currenPosition["O"]=1;
			$currentPosition["B"]=1;