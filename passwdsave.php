<?php

$s9jsfile = 'json/s9pass.json';
$l3jsfile = 'json/l3pass.json';

$type   = $_GET['type'];
$passwd   = $_GET['passwd'];

if ($type == 'l3') {
    $file = $l3jsfile;
    $a = json_decode(file_get_contents($file),true);
}
else if ($type == 's9')	{
    $file = $s9jsfile;
    $a = json_decode(file_get_contents($file),true);
}
else {
    $html .= "BAD Type!";
}

if ($type) {

    $a['password'] = $passwd;

    $fpd = fopen($file, 'w');
    fwrite($fpd, json_encode($a,JSON_PRETTY_PRINT));
    fclose($fpd);

    print '<br><br>Default Password Saved!<br>';
}