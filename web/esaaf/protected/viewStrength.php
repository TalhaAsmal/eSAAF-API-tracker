<html>
    <head>
        <title>Strength Report</title>
    </head>

    <body>
<?php
    include("header.inc.php");
	connect_db();
    $query_data="SELECT * FROM `soldier_strength` ORDER BY Division, Company, Squad, Rank, Name";
    $result_data=mysql_query($query_data);
	
    $num=mysql_num_rows($result_data);
    
    echo "<table align=\"center\" border=\"2\" cellspacing=\"2\" cellpadding=\"2\">
            <tr>";
    //Get table headings
    for($i=1;$i<mysql_num_fields($result_data);$i++) {
        $meta=mysql_fetch_field($result_data,$i);
        $heading=$meta->name;
        echo "<th>$heading</th>";
    }
    echo "</tr>";
	
    //Get Data
    for ($i=0;$i<$num;$i++) {
        echo "<tr>";
        for($field=1;$field<mysql_num_fields($result_data);$field++) { //Get data values
            $meta=mysql_fetch_field($result_data,$field);
            $name=$meta->name;
            $value=mysql_result($result_data,$i,"$name");
            echo "<td>$value</td>";
        }		
        echo "</tr>";
    }
    
    echo "</table>";
    
    mysql_close();
?>
        <a href="index.htm">Return Home</a>
    </body>
</html>