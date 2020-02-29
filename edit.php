<?php

$jsfile = 's9list.json';
$l3jsfile = 'l3list.json';
$jsfileand = 's9and.json';
$jsfilegrg = 's9georg.json';

$id   = $_GET['id'];
$type = $_GET['type'];

if ($type == 'l3') {
	$a = json_decode(file_get_contents($l3jsfile),true);
}
else if (preg_match('/AB/', $id)) {
	$a = json_decode(file_get_contents($jsfileand),true);
}
else if (preg_match('/G/', $id)) {
	$a = json_decode(file_get_contents($jsfilegrg),true);
}
else             {
	$a = json_decode(file_get_contents($jsfile),true);
}


$ip  = $a[$id]['ip'];
if (!$ip) {
		print "Bad ID: $id<br>";
}
else {
	print '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Edit</title></head><body>
	<form action="editsave.php">
		ID: <input type=text name=id value="'.$a[$id]['id'].'" size=3> 
		IP: <input type=text name=ip value="'.$a[$id]['ip'].'" size=8> 
		Model: <input type=text name=model value="'.$a[$id]['model'].'" size=5> 
		Fan check: <input type=text name=fanck value="'.$a[$id]['fanck'].'" size=1> 
		Fan mode: <input type=text name=fanmod value="'.$a[$id]['fanmod'].'" size=1> 
		Disabled: <input type=text name=disabled value="'.$a[$id]['disabled'].'" size=1> 
		Comment: <input type=text name=comment value="'.$a[$id]['comment'].'" size=30>
		<input type="hidden" name="type" value='.$type.'>
		<input type=submit value="Save">
	</form>
	</body>';
}

?>
