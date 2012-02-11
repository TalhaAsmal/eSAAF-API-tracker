<?php

	/* Tutorial by AwesomePHP.com -> www.AwesomePHP.com */
	/* Function: download remote file */
	/* Parameters: $url -> to download | $dir -> where to store file |
		$file_name -> store file as this name - if null, use default*/

	function downloadRemoteFile($url,$dir,$file_name = NULL){
		if($file_name == NULL){ $file_name = basename($url);}
		$url_stuff = parse_url($url);
		$port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;

		$fp = fsockopen($url_stuff['host'], $port);
		if(!$fp){ return false;}

		$query  = 'GET ' . $url_stuff['path'] . " HTTP/1.0\n";
		$query .= 'Host: ' . $url_stuff['host'];
		$query .= "\n\n";

		fwrite($fp, $query);

		while ($tmp = fread($fp, 8192)) {
			$buffer .= $tmp;
		}

		preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
		$file_binary = substr($buffer, - $parts[1]);
		if($file_name == NULL){
			$temp = explode(".",$url);
			$file_name = $temp[count($temp)-1];
		}
		$file_open = fopen($dir . "/" . $file_name,'w');
		if(!$file_open){ return false;}
		fwrite($file_open,$file_binary);
		fclose($file_open);
		return true;
	}

	if(!isset($_REQUEST['Submit'])) {
		echo '<form action="'.$PHP_SELF.'" method="POST">';
		echo '<input type="text" name="url" id="url" />';
		echo '<input id="Submit" type="Submit" name="Submit" value="Submit" />';
		echo '</form>';
	}
	else {
		$url=$_REQUEST['url'];
		$dir="torrents";

		if(downloadRemoteFile($url,$dir))
			echo "File downloaded successfully"

?> 