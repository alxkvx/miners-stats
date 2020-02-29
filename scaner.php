<?php

error_reporting(E_ERROR | E_PARSE);

$html = '<head><link href="main.css" type="text/css" rel="stylesheet"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/scaner.js"></script>
<title>Scan</title></head><body>
<table border=0 cellspacing=0 cellpadding=3>
<tr>
<td>IP range:</td>
<td><input name="oct1" type="text" size="2" value=""></td>
<td><input name="oct2" type="text" size="2" value=""></td>
<td><input name="oct3" type="text" size="2" value=""></td>
<td><input name="oct4" type="text" size="2" value=""></td>
<td>-</td>
<td><input name="oct4b" type="text" size="2" value=""></td>
<td>Skip Existing:<input id="skip" type="checkbox" value="1"></td>
<td><input id="scanbtn" name="scanbtn" type="button" value="SCAN"></td></tr>
</table>
<div id="scandiv"></div>
</body>';

print $html;

?>

