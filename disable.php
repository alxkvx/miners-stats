<?php

$file = 'json/s9.json';

$s9  = json_decode(file_get_contents($file),true);

$fp = fopen('json/s9list-saved.json', 'w');
fwrite($fp, json_encode($s9,JSON_PRETTY_PRINT));
fclose($fp);

$id 	= $_GET['id'];
$enable	= $_GET['enable'];

if ($enable == 1) {
	$s9[$id]['disabled'] = 0;
	$status = 'Enabled';
}
else		{
	$s9[$id]['disabled'] = 1;
	$status = 'Disabled';
}

echo '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Save</title></head><body>
ID: '.$id.' '.$status.'!<br></body>';

$fpd = fopen($file, 'w');
fwrite($fpd, json_encode($s9,JSON_PRETTY_PRINT));
fclose($fpd);

?>
