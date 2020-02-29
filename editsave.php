<?php

$jsfile = 'json/s9.json';
$l3jsfile = 'json/l3.json';

$id   = $_GET['id'];
$type = $_GET['type'];

if ($type == 'l3') {
	$file = $l3jsfile;
	$a = json_decode(file_get_contents($file),true);
}
else if ($type == 's9') {
	$file = $jsfile;
	$a = json_decode(file_get_contents($file),true);
}
else {
	echo "Bad type!";
}

$fp = fopen('list-saved.json', 'w');
fwrite($fp, json_encode($a,JSON_PRETTY_PRINT));
fclose($fp);

$a[$id]['id']		= $id;
$a[$id]['ip']		= $_GET['ip'];
$a[$id]['model']	= $_GET['model'];
$a[$id]['fanck']	= $_GET['fanck'];
$a[$id]['fanmod']	= $_GET['fanmod'];
$a[$id]['comment']	= $_GET['comment'];
$a[$id]['disabled']	= $_GET['disabled'];

print '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Save</title></head><body>
		File: '.$file.'<br> 
		ID: '.$a[$id]['id'].'<br> 
		IP: '.$a[$id]['ip'].'<br>
		Model: '.$a[$id]['model'].' <br>
		Fan check: '.$a[$id]['fanck'].' <br>
		Fan mode: '.$a[$id]['fanmod'].' <br>
		Disabled: '.$a[$id]['disabled'].'<br> 
		Comment: '.$a[$id]['comment'].'</body>';

$fpd = fopen($file, 'w');
fwrite($fpd, json_encode($a,JSON_PRETTY_PRINT));
fclose($fpd);

?>
