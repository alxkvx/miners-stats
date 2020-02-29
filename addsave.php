<?php

$jsfile = 'json/s9.json';
$l3jsfile = 'json/l3.json';
$rigfile = 'json/rig.json';
$avalonfile = 'json/avalon.json';

$id   = $_GET['id'];
$type = $_GET['type'];

$html = '';

if ($type == 'l3') {
	$file = $l3jsfile;
	$a = json_decode(file_get_contents($file),true);
}
else if ($type == 's9')	{
	$file = $jsfile;
	$a = json_decode(file_get_contents($file),true);
}
else if ($type == 'rig') {
	$file = $rigfile;
	$a = json_decode(file_get_contents($file),true);
}
else if ($type == 'avalon') {
	$file = $avalonfile;
	$a = json_decode(file_get_contents($file),true);
}
else {
	$html .= "BAD Type!";
}

if ($type) {

	if (!$a[$id]['id']) {

		if ($type == 'rig') {
			$a[$id]['id']	= $id;
			$a[$id]['ip']	= $_GET['ip'];
			$a[$id]['port']	= $_GET['port'];
			$a[$id]['name']= $_GET['name'];
			$a[$id]['gputype']	= $_GET['gputype'];
			$a[$id]['memtype']	= $_GET['memtype'];
			$a[$id]['gpusnum']	= $_GET['gpusnum'];
			$a[$id]['disabled']	= $_GET['disabled'];

			$html.='<br><br>File: '.$file.'<br> 
			ID: '.$a[$id]['id'].'<br> 
			IP: '.$a[$id]['ip'].'<br>
			Port: '.$a[$id]['port'].'<br>
			Name: '.$a[$id]['name'].' <br>
			GPU Type: '.$a[$id]['gputype'].' <br>
			MEM Type: '.$a[$id]['memtype'].' <br>
			GPUs: '.$a[$id]['gpusnum'].' <br>';
		}
		elseif ($type == 'avalon') {
			$a[$id]['id'] = $id;
			$a[$id]['ip'] = $_GET['ip'];
			$a[$id]['groups'] = $_GET['groups'];

			$html.='<br><br>File: '.$file.'<br> 
			ID: '.$a[$id]['id'].'<br> 
			IP: '.$a[$id]['ip'].'<br>
			Groups: '.$a[$id]['groups'].'<br>';
		}
		else {

			$a[$id]['id'] = $id;
			$a[$id]['ip'] = $_GET['ip'];
			$a[$id]['model'] = $_GET['model'];
			$a[$id]['fanck'] = $_GET['fanck'];
			$a[$id]['fanmod'] = $_GET['fanmod'];
			$a[$id]['comment'] = $_GET['comment'];
			$a[$id]['disabled'] = $_GET['disabled'];

			$html .= '<br><br>File: ' . $file . '<br> 
			ID: ' . $a[$id]['id'] . '<br> 
			IP: ' . $a[$id]['ip'] . '<br>
			Model: ' . $a[$id]['model'] . ' <br>
			Fan check: ' . $a[$id]['fanck'] . ' <br>
			Fan mode: ' . $a[$id]['fanmod'] . ' <br>';
		}
		$fpd = fopen($file, 'w');
		fwrite($fpd, json_encode($a,JSON_PRETTY_PRINT));
		fclose($fpd);
	}
	else {
		$html.= "ID: $id already exists!<br>";
	}
}

echo $html;

?>
