<?php

$jsfile = 'json/s9.json';
$l3jsfile = 'json/l3.json';

$id   	= $_GET['id'];
$type   = $_GET['type'];
$pool[0] = $_GET['pool0'];
$pool[1] = $_GET['pool1'];
$pool[2] = $_GET['pool2'];
$user[0] = $_GET['user0'];
$user[1] = $_GET['user1'];
$user[2] = $_GET['user2'];
$freq[0] = $_GET['freq0'];
$freq[1] = $_GET['freq1'];
$freq[2] = $_GET['freq2'];
$volt[0] = $_GET['volt0'];
$volt[1] = $_GET['volt1'];
$volt[2] = $_GET['volt2'];
$temp    = $_GET['temp'];
$sensor  = $_GET['sensor'];
$nofanck = $_GET['fanck'];
$loadfreq= $_GET['loadfreq'];

function api($ip,$command) {
	$socket = fsockopen($ip, 4028, $err_code, $err_str, 0.1);
	if (!$socket) {	
		$socket2 = fsockopen($ip, 80, $err_code, $err_str, 0.1);
		if ($socket2)   {return 1;}
		else            {return 0;}
	}
	$data = '{"id":1,"jsonrpc":"2.0","command": "'. $command . '"}' . "\r\n\r\n";
	stream_set_timeout($socket, 1);
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fread($socket, 4028); }
	if ($socket) {  fclose($socket); }
	$buff = substr($buffer,0,strlen($buffer)-1);
	$buff = preg_replace('/}{/','},{',$buff);
	$buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
	if (!json_decode($buff)) { print "BAD json, error: " . json_last_error();}
	else { $json = json_decode($buff,true);}
	return $json;
}

$html = '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Conf Save</title></head><body>';

if ($type == 'l3') {
	$file = $l3jsfile;
	$a = json_decode(file_get_contents($file),true);
	$pswd_json = json_decode(file_get_contents('json/l3pass.json'),true);
}
else if ($type == 's9')	{
	$file = $jsfile;
	$a = json_decode(file_get_contents($file),true);
	$pswd_json = json_decode(file_get_contents('json/s9pass.json'),true);
}
else {
	$html .= "BAD Type!";
}

if ($type) {

	if (!$a[$id]['id']) {
	
		$html.= "ID: $id doesnt exists!";
	}
	else {
		$ip = $a[$id]['ip'];

		$ssh_passwd = $pswd_json['password'] or $ssh_passwd = 'admin';
		if ($loadfreq == 'true') {
			$json = api($ip,'stats');
			for ($i=0; $i<3; $i++) {
				$y = 6 + $i;
				$freq[$i] = $json['STATS'][1]["freq_avg$y"];
				$volt[$i] = $json['STATS'][1]["voltage$y"];
			}
		}
		$freqs = "$freq[0],$freq[1],$freq[2],,,";
		$volts = "$volt[0],$volt[1],$volt[2],,,";
		if ($sensor == 'true') {$sensor_check = true;}
		else {$sensor_check = false;}

		$conf['fan-ctrl'] = 'temp';
		$conf['api-port'] = '4028';
		$conf['no-pre-heat'] = true;
		$conf['fan-temp'] = '81';
		$conf['bitmain-freq'] = $freqs;
		$conf['api-allow'] = 'W:0\/0';
		$conf['bitmain-voltage'] = $volts;
		$conf['config-format-revision'] = '1';
		$conf['bitmain-use-vil'] = true;
		$conf['api-listen'] = true;
		$conf['no-sensor-scan'] = $sensor_check;
		$conf['multi-version'] = '4';
		$conf['fan-dangerous-temp'] = $temp;
		if ($nofanck == 'true') { $conf['min-fans'] = "0";}
		for ($x=0;$x<3;$x++) {
			if ($pool[$x]) {
				$conf['pools'][$x]['_id'] = ''.($x+1).'';
				$conf['pools'][$x]['url'] = $pool[$x];
				$conf['pools'][$x]['user'] = $user[$x];
				$conf['pools'][$x]['pass'] = '1';
			}
		}
		$html.='Conf Saved:<br><br>File: cgminer.conf<br> 
		ID: '.$id.'<br> 
		IP: '.$ip.'<br><pre>'.stripslashes(json_encode($conf,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)).'</pre>';
			
		$fpd = fopen('cgminer.conf', 'w');
		fwrite($fpd, stripslashes(json_encode($conf,JSON_UNESCAPED_SLASHES)));
		fclose($fpd);
		if ($loadfreq == 'true' && ($json==0 || $json==1)) { echo "miner $id down"; }
		else {
			$connection = ssh2_connect($ip, 22);
			ssh2_auth_password($connection, 'root', $ssh_passwd);
			ssh2_scp_send($connection, 'cgminer.conf', '/etc/cgminer.conf', 0644);
			$html.= "Config uploaded!";
		}
	}
}

echo $html;
