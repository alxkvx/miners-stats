<?php

function api($ip,$command,$opt) {
	
	$socket = fsockopen($ip, 4028, $err_code, $err_str, 0.5);
	if (!$socket) {
			$socket2 = fsockopen($ip, 22, $err_code, $err_str, 0.5);
					if ($socket2)   {return 1;}
					else            {return 0;}
	}
	$data = '{"id":1,"jsonrpc":"2.0","command": "'. $command . '","parameter": "'.$opt.'"}' . "\r\n\r\n";
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fgets($socket, 4028); }
	if ($socket) {  fclose($socket); }
	$buff = substr($buffer,0,strlen($buffer)-1);
	$buff = preg_replace('/}{/','},{',$buff);
	$buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
	print "command: ".$command." param: ".$opt."<br>". $buff . '<br><br>';
	if (!json_decode($buff)) { print "BAD json, error: " . json_last_error();}
	else { $json = json_decode($buff,true);}
	return $json;
}

$ip 	= $_GET['ip'];
$command= $_GET['command'];
$opt 	= $_GET['opt'];
$url 	= $_GET['url'];
$user 	= $_GET['user'];

if ($command=='addpool') {
	api($ip,$command,"$url,$user,1");
}
else if ($command=='restartminer') {
	$connection = ssh2_connect($ip, 22);
	ssh2_auth_password($connection, 'root', '1qazXsw2');
	ssh2_exec($connection, '/etc/init.d/cgminer restart');
	echo "$ip miner restarted";
}
else if ($command=='addnswtchpool') {
	$out = api($ip,'addpool',"$url,$user,1");
	$poolmsg = $out['STATUS'][0]['Msg'];
	preg_match('/Added pool (\d+):/', $poolmsg, $poolid);
	#echo "<br>pool id: ".$poolid[1]."<br>";
	api($ip,'switchpool',$poolid[1]);

}	
else {
	api($ip,$command,$opt);
}
?>
