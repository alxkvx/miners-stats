<?php

error_reporting(E_ERROR | E_PARSE);
require_once('files.php');

$oct1 = $_GET['oct1'];
$oct2 = $_GET['oct2'];
$oct3 = $_GET['oct3'];
$oct4 = $_GET['oct4'];
$oct4b = $_GET['oct4b'];
$skip = $_GET['skip'];

$s9list = json_decode(file_get_contents($s9jsonfile),true);
$l3list = json_decode(file_get_contents($l3jsonfile),true);
$s9and  = json_decode(file_get_contents($l3andjsonfile),true);

$s9 = [];

foreach ($s9list as $x) { array_push($s9, $x['ip']); }
foreach ($l3list as $x) { array_push($s9, $x['ip']); }
foreach ($s9and as $x) { array_push($s9, $x['ip']); }
foreach ($riglist as $val) { array_push($s9, $val[0]); }

function check_api($ip) {
	
	$socket = fsockopen($ip, 4028, $err_code, $err_str, 0.05);
	if (!$socket) {
		$socket2 = fsockopen($ip, 22, $err_code, $err_str, 0.1);
		if ($socket2)   {
			$socket3 = fsockopen($ip, 3335, $err_code, $err_str, 0.05);
			if ($socket3) {return array(3,0);}
			else 	{return array(1,0);}
		}
		else        {return array(0,0);}
	}
	$data = '{"id":1,"jsonrpc":"2.0","command": "summary+pools+stats"}' . "\r\n\r\n";
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fgets($socket, 4028); }
	if ($socket) {  fclose($socket); }
	$buff = substr($buffer,0,strlen($buffer)-1);
	$buff = preg_replace('/}{/','},{',$buff);
	$buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
	if (!json_decode($buff)) { print "BAD json, error: " . json_last_error();}
	else { $json = json_decode($buff,true);}
	
	$th5s	= $json['summary'][0]['SUMMARY'][0]['GHS 5s'];
	$worker	= $json['pools'][0]['POOLS'][0]['User'];
	$type	= $json['stats'][0]['STATS'][0]['Type'];
	$pool[0]= $json['pools'][0]['POOLS'][0]['URL'];
	$pool[1]= $json['pools'][0]['POOLS'][1]['URL'];
	return array(2,$worker,$th5s,$pool,$type);
}

$html = '<br><table border=1 cellspacing=0 cellpadding=5><tr class=head>
<td><input id="all" type=checkbox></td><td>ID</td><td>IP</td><td>Type</td><td>Miner</td><td>Pool 0</td><td>User 0</td><td>Hashrate</td></tr>';

for ($i=$oct4;$i<$oct4b;$i++) {

	$ip = "$oct1.$oct2.$oct3.$i";
	if ($skip == 1) {
		if (in_array($ip, $s9)) {	continue;	}
	}
	$result = check_api($ip);
	
	$res = $result[0];
	$worker = $result[1];
	$pool = $result[3];
	$miner = $result[4];
	$pool[0] = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool[0]); 
	$pool[1] = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool[1]); 
	$th = number_format($result[2],0);
	if 		(preg_match('/s9/i', $miner)) { $type = 'Antminer S9';}
	else if (preg_match('/l3/i', $miner)) { $type = 'Antminer L3+';}
	if 	($res == 2) { $html .= "<tr><td><input class='miners' type='checkbox' chkid=\"$i\" ip=\"$ip\" miner=\"$type\"></td><td><input id=\"inp_$i\" type='text' value=$i size='3'></td><td>$ip</td><td>$type</td><td>$miner</td><td>$pool[0]</td><td>$worker</td><td>$th</td></tr>";}
	else if ($res == 1) { $html .= "<tr><td></td><td></td><td>$ip</td><td>SSH only</td><td colspan='4'></td></tr>";}
	else if ($res == 3) { $html .= "<tr><td></td><td></td><td>$ip</td><td>GPU rig</td><td colspan='4'></td></tr>";}
}

print $html."</table><br><input id=\"addmass\" type=submit value=\"ADD SELECTED\">";

?>
