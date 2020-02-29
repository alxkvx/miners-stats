<?php

$jsfile = 'json/s9.json';

$s9  = json_decode(file_get_contents($jsfile),true);
$s9and = json_decode(file_get_contents($jsfileand),true);
$s9grg = json_decode(file_get_contents($jsfilegrg),true);

$fp = fopen('s9list-saved.json', 'w');
fwrite($fp, json_encode($s9,JSON_PRETTY_PRINT));
fclose($fp);

$id 	= $_GET['id'];

if (preg_match('/AB/', $id)) {
        $file = $jsfileand;
        $s9 = $s9and;
}
else if (preg_match('/G/', $id)) {
        $file = $jsfilegrg;
        $s9 = $s9grg;
}
else             {
        $file = $jsfile;
}

unset($s9[$id]);

print '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Save</title></head><body>
		<br>ID: '.$id.' removed!</body>';

$fpd = fopen($file, 'w');
fwrite($fpd, json_encode($s9,JSON_PRETTY_PRINT));
fclose($fpd);


?>
