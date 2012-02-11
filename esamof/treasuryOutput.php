<html>
	<head>
		<title>Treasury History</title>
	</head>
	<body>
		
<?php
	if(!isset($_REQUEST['Submit'])) {
		$countries=array("South Africa");
?>	
		<h1>Output Selection</h1>

		<form method="post" action="<?php echo $PHP_SELF;?>">
			<h2>Select country</h2>
			<?php
			foreach($countries as $country) {
				$countryLink=str_replace(" ", "-", $country);
				echo '<input type="Radio" name="Country" value="'.$countryLink.'" />'.$country.'<br />';
			}
?>			
			<h2>Select output period</h2>
			Leave blank for current month <br />
			<input type="Radio" name="Month" value="January" /> January, 2011 <br />
			<input type="Radio" name="Month" value="February" /> February, 2011 <br />
			<input type="Radio" name="Month" value="March" /> March, 2011 <br />
			<input type="Radio" name="Month" value="April" /> April, 2011 <br />
			
			<h2>Select output type(s)</h2>
			
			<input type="Checkbox" name="Output[]" value="Raw" />Raw Treasury Data <br />
			<input type="Checkbox" name="Output[]" value="Delta" />Treasury Delta<br />
			<input type="Checkbox" name="Output[]" value="DeltaPerHour" />Treasury Delta Per Hour<br />
			<input type="Checkbox" name="Output[]" value="DeltaPerDay" />Treasury Delta Per Day<br />
			
			<br />
			<input type="Submit" name="Submit" value="Submit" />
		</form>
<?php		
	}
	else
	{
		include("dbinfo.inc.php");
		$output=$_REQUEST['Output'];
		$country=$_REQUEST['Country'];

		date_default_timezone_set('America/Los_Angeles');
		
		if(isset($_REQUEST['Month']))
			$month=$_REQUEST['Month'];
		else {
			if((int) date("j") > 5)
				$month=(string) date("F");
			else
				$month=(string) date("F",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
		}
		
		
		connect_db();

		$query="SELECT * FROM `treasury_tracker_".$month."_".$country."`";
		$transferTable='treasury_transfers_'.$month.'_'.$country;
		$result=mysql_query($query) or die(mysql_error()); 
		
		//Output raw treasury data
		if(in_array('Raw',$output)) {
			echo "<h1>Raw Treasury Data</h1>";
			echo '<table cellspacing="2" cellpadding="2" border="2">
					<tr>';
			
			while($field=mysql_fetch_field($result)) {
				$header=$field->name;
				$fieldNames[]=$header;
				echo '<th>'.$header."</th>";
			}
			echo '</tr>';
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				echo '<tr>';
				foreach($fieldNames as $header) {
					$value=mysql_result($result,$i,$header);
					echo '<td>'.$value.'</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
			echo '<br /> <br />';
		}
	
		//Output treasury delta
		if(in_array("Delta",$output)) {
			echo "<h1>Treasury Delta</h1>";
			echo '<table cellspacing="2" cellpadding="2" border="2">
					<tr>
						<td>Code</td>';
			for($column=3;$column<mysql_num_fields($result);$column++) {
				$currentField=mysql_fetch_field($result,$column)->name;
				$previousField=mysql_fetch_field($result,$column-1)->name;
				$currentTime=explode(" ",$currentField);
				$currentSplitTime=explode(":",$currentTime[0]);
				$previousTime=explode(" ",$previousField);
				$previousSplitTime=explode(":",$previousTime[0]);
				$minuteDifference=(float) ($currentSplitTime[1]-$previousSplitTime[1]);
				$hourDifference=(float) ($currentSplitTime[0]-$previousSplitTime[0]);
				$dayDifference=(float) ($currentTime[1]-$previousTime[1]);
				$timeDifference[]=$dayDifference*24.0+$hourDifference+$minuteDifference/60.0;
				echo '<th>'.$previousField.'-'.$currentField.'</th>';
			}
			echo '</tr>';
			
			for($row=0;$row<mysql_num_rows($result);$row++) {
				echo '<tr>';
				echo '<td>'.mysql_result($result,$row,"Code").'</td>';
				for($column=3;$column<mysql_num_fields($result);$column++) {
					$currentValue=mysql_result($result,$row,mysql_fetch_field($result,$column)->name);
					$previousValue=mysql_result($result,$row,mysql_fetch_field($result,$column-1)->name);
					$difference=$currentValue-$previousValue;
					echo '<td>'.$difference.'</td>';
				}
				echo '</tr>';
			}
			
			echo '<tr>
					<td>Time Difference</td>';
			foreach($timeDifference as $thisTime) {
				echo "<td> $thisTime </td>";
			}
			echo '</tr>
				</table>';
			echo '<br /> <br />';
		}
	
		//Output treasury delta/hour
		if(in_array("DeltaPerHour",$output)) {
			echo "<h1>Treasury Delta/hour</h1>";
			echo '<table cellspacing="2" cellpadding="2" border="2">
					<tr>
						<th>Code</th>';
			for($column=3;$column<mysql_num_fields($result);$column++) {
				$currentField=mysql_fetch_field($result,$column)->name;
				$previousField=mysql_fetch_field($result,$column-1)->name;
				$currentTime=explode(" ",$currentField);
				$currentSplitTime=explode(":",$currentTime[0]);
				$previousTime=explode(" ",$previousField);
				$previousSplitTime=explode(":",$previousTime[0]);
				$minuteDifference=(float) ($currentSplitTime[1]-$previousSplitTime[1]);
				$hourDifference=(float) ($currentSplitTime[0]-$previousSplitTime[0]);
				$dayDifference=(float) ($currentTime[1]-$previousTime[1]);
				$timeDifference[]=$dayDifference*24.0+$hourDifference+$minuteDifference/60.0;
				echo '<th>'.$previousField.'-'.$currentField.'</th>';
			}
			echo '</tr>';
			
			for($row=0;$row<mysql_num_rows($result);$row++) {
				echo '<tr>';
				echo '<td>'.mysql_result($result,$row,"Code").'</td>';
				for($column=3;$column<mysql_num_fields($result);$column++) {
					$currentValue=mysql_result($result,$row,mysql_fetch_field($result,$column)->name);
					$previousValue=mysql_result($result,$row,mysql_fetch_field($result,$column-1)->name);
					$difference=round(($currentValue-$previousValue)/(float) $timeDifference[$column-3],2);
					echo '<td>'.$difference.'</td>';
				}
				echo '</tr>';
			}
			
			echo '</table>';
			echo '<br /> <br />';
		}
	
		//Output Treasury Delta per day
		if(in_array("DeltaPerDay",$output)) {
			echo "<h1>Treasury Delta/day</h1>";
			
			for($column=3;$column<mysql_num_fields($result);$column++) {
				$currentField=mysql_fetch_field($result,$column)->name;
				$explodedHeader=explode(" ",$currentField);
				for($row=0;$row<mysql_num_rows($result);$row++) {
					$code=mysql_result($result,$row,"Code");
					$day=(int) $explodedHeader[1];
					$transferQuery="SELECT `$day` from `$transferTable` WHERE `Code`='$code'";
					if($transferResult=mysql_query($transferQuery)) {
						$row2=mysql_fetch_assoc($transferResult);
						$transferred[$code][$day]=$row2[$day]; 
					}
					else { $transferred[$code][$day]=0; }
					$currentValueDay[$code][$day]=mysql_result($result,$row,$currentField);
				}
			}

			echo '<table cellspacing="2" cellpadding="2" border="2">
					<tr>
						<th>Code</th>';
			foreach($currentValueDay[$code] as $day => $value) {
				echo '<th>'.$day.'</th>';
			}	
			echo '</tr>';
			
			foreach($currentValueDay as $code => $valuesArray) {
				echo '<tr>
						<td>'.$code.'</td>';
				foreach($valuesArray as $day => $value) {
					@$dayDelta=$value-$currentValueDay[$code][$day-1]+$transferred[$code][$day];
					echo '<td>'.$dayDelta.'</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
			echo '<br /> <br />';
		}
		
		/*for($row=0;$row<mysql_num_rows($result);$row++) {
			echo '<tr>';
			echo '<td>'.mysql_result($result,$row,"Code").'</td>';
			for($column=3;$column<mysql_num_fields($result);$column++) {
				$fieldName=mysql_fetch_field($result,$column)->name;
				$day=explode(" ",$fieldName);
				$currentValue[$day[1]]+=mysql_result($result,$row,$fieldName);
			}
			foreach($currentValue[] as $value) {
				echo '<td>'.$value.'</td>';
			}
			
			echo '</tr>';
		}*/	

		mysql_close();
	}
?>
	</body>
</html>