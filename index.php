<?php

include 'functions.php';

$s9 = json_decode(file_get_contents('json/s9.json'),true);
$l3 = json_decode(file_get_contents('json/l3.json'),true);
$rig = json_decode(file_get_contents('json/rig.json'),true);
$avalon = json_decode(file_get_contents('json/avalon.json'),true);

function miner_details($type,$arr) {

	$ip     = $arr['ip'];
	$disable = $arr['disabled'];

	if ($disable == 1) {  return array(0,0,0); }
	
	$json = api($ip,'summary+stats');
	
	if	 ($json == 0) { return array(0,0,0); }
	else if ($json == 1) {return array(1,0,0); }
	
	$ghsav	= $json['summary'][0]['SUMMARY'][0]['GHS av'];
	$fw_type= $json['stats'][0]['STATS'][0]['Type'];
	
	if ($ghsav>20000) {$ghsav = 16000;}
	if ($type == 's9') {
		for ($x=0;$x<3;$x++) {
			if ($fw_type == 'Antminer S9k') {
				$y = 1 + $x;
				$chip_temp[$x]       = $json['stats'][0]['STATS'][1]["temp$y"];
			}
			else {
				$y = 6 + $x;
				$chip_temp[$x]       = $json['stats'][0]['STATS'][1]["temp2_$y"];
			}
		}
		if ($chip_temp[0] < 16) {
			if      ($chip_temp[1] < 16) {$avgtemp = $chip_temp[2];}
			else if ($chip_temp[2] < 16) {$avgtemp = $chip_temp[1];}
			else    {$avgtemp = array_sum($chip_temp)/2;}
		}
		else if ($chip_temp[1] < 16) {
			if      ($chip_temp[0] < 16) {$avgtemp = $chip_temp[2];}
			else if ($chip_temp[2] < 16) {$avgtemp = $chip_temp[0];}
			else    {$avgtemp = array_sum($chip_temp)/2;}
		}
		else if ($chip_temp[2] < 16) {
			if      ($chip_temp[0] < 16) {$avgtemp = $chip_temp[1];}
			else if ($chip_temp[1] < 16) {$avgtemp = $chip_temp[0];}
			else    {$avgtemp = array_sum($chip_temp)/2;}
		}
		else {
			$avgtemp = array_sum($chip_temp)/3;
		}
	}
	else {
		for ($x=0;$x<4;$x++) {
			$y = 1 + $x;
			$chip_temp[$x]      = $json['stats'][0]['STATS'][1]["temp2_$y"];
		}
		$avgtemp = round(array_sum($chip_temp)/4,2);
	}
	return  array(2,$ghsav,$avgtemp);
}

$s9all = count($s9);
$l3all = count($l3);
$rigall = count($rig);
$avalonall = count($avalon);

$s9hash = 0;
$s9temp = 0;
$s9on = 0;
$s9off = 0;
$s9ssh = 0;
$s9t = 0;

foreach($s9 as $x) {

	$vars = miner_details('s9',$x);
	$state	= $vars[0];
	$s9hash+= $vars[1];
	$s9temp+= $vars[2];
	if ($vars[2]>15) {$s9t++;}
	if ($state == 0) { $s9off+= 1; }
	else if ($state == 1) { $s9ssh+= 1; }
	else if ($state == 2) { $s9on+= 1; }
}

$l3hash = 0;
$l3temp = 0;
$l3on = 0;
$l3off = 0;
$l3ssh = 0;

foreach($l3 as $x) {

	$vars = miner_details('l3',$x);
	$state	= $vars[0];
	$l3hash+= $vars[1];
	$l3temp+= $vars[2];
	if ($state == 0) { $l3off++; }
	else if ($state == 1) { $l3ssh++; }
	else if ($state == 2) { $l3on++; }
}

$avalonhash = 0;
$avalontemp = 0;
$avalonon = 0;
$avalonoff = 0;

foreach($avalon as $x) {

	$id = $x['id'];
	$ip = $x['ip'];
	$groups = $x['groups'];

	$json = api($ip,'stats');

	if	 ($json == 0) {
		$avalonoff++;
	}
	else {

		for ($i = 0; $i < $groups; $i++) {

			for ($x = 0; $x < 5; $x++) {
				$z = $x + 1;
				$avid = "MM ID$z";
				$avset[$x] = $json['STATS'][$i][$avid];
				if (!$avset[$x]) {
					continue;
				}
				$arr = explode(" ", $avset[$x]);
				$ctemp0 = preg_replace("/Temp\[(.*)\]/", "\$1", $arr[13]);
				$ghs5s = preg_replace("/GHSmm\[(.*)\]/", "\$1", $arr[25]);

				$avalonhash += $ghs5s;
				$avalontemp += $ctemp0;
				$avalonon++;
			}
		}
	}
}

$righash = 0;
$rigtemp = 0;
$rigon = 0;
$rigoff = 0;

foreach($rig as $x) {

	$ip		= $x['ip'];
	$port 	= $x['port'];
	$gpus	= $x['gpusnum'];

	$socket = fsockopen($ip, $port, $err_code, $err_str,0.5);
	if (!$socket) {
		$rigoff++;
		continue;
	}
	$data = '{"id":1,"jsonrpc":"2.0","method":"miner_getstat1"}' . '\r\n\r\n';
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fgets($socket); }
	if ($socket) { 	fclose($socket); }
	$json = json_decode($buffer,true);

	$miner		= $json['result'][0];
	$elapsed	= $json['result'][1];
	$total_eth	= $json['result'][2];
	$eth_mh		= $json['result'][3];
	$total_xvg	= $json['result'][4];
	$xvg_mh		= $json['result'][5];
	$temp_fan	= $json['result'][6];
	$pools		= $json['result'][7];
	$eth_pool_inv_sw= $json['result'][8];
	$eth_acc_shrs	= $json['result'][9];
	$eth_rej_shrs	= $json['result'][10];
	$eth_inv_shrs	= $json['result'][11];
	$xvg_acc_shrs	= $json['result'][12];
	$xvg_rej_shrs	= $json['result'][13];
	$xvg_inv_shrs	= $json['result'][14];

	$miner_arr = explode(" - ", $miner);
	$miner_name = $miner_arr[0];
	$coin = $miner_arr[1];

	$eth_pool = preg_replace("/(.*);.*/","\$1",$pools);
	$eth_all = preg_replace("/(\d+);.*/","\$1",$total_eth);
	$xvg_all = preg_replace("/(\d+);.*/","\$1",$total_xvg);
	$eth_all = number_format($eth_all/1000, 1);
	$xvg_all = number_format($xvg_all/1000000, 2);

	$tavg = 0;
	$gpu_eth = [];
	$gpu_temp =[];
	$gpu_fan = [];
	$gpus_all = explode(";", $eth_mh);
	$temp_fanall = explode(";", $temp_fan);
	$gpus_num = count($gpus_all);
	$j=0;
	for ($i=0;$i<$gpus_num;$i++){
		$gpu_eth[$i]    = number_format($gpus_all[$i]/1000, 2);
		$gpu_temp[$i]   = $temp_fanall[$j];
		$gpu_fan[$i]    = $temp_fanall[$j+1];
		$tavg+= $gpu_temp[$i];
		$j+=2;
	}

	$tavg = round($tavg/$gpus, 1);
	$rigtemp += $tavg;
	$righash += $eth_all;
	$rigon++;
}

$summary['S9']['all'] = $s9all;
$summary['S9']['on'] = $s9on;
$summary['S9']['off'] = $s9off;
$summary['S9']['ssh'] = $s9ssh;
$summary['S9']['hash'] = round($s9hash);
$summary['S9']['temp'] = number_format($s9temp/$s9t,2);
$summary['L3']['all'] = $l3all;
$summary['L3']['on'] = $l3on;
$summary['L3']['off'] = $l3off;
$summary['L3']['ssh'] = $l3ssh;
$summary['L3']['hash'] = round($l3hash);
$summary['L3']['temp'] = number_format($l3temp/$l3on,2);
$summary['rig']['all'] = $rigall;
$summary['rig']['on'] = $rigon;
$summary['rig']['off'] = $rigoff;
$summary['rig']['hash'] = round($righash);
$summary['rig']['temp'] = number_format($rigtemp/$rigon,2);

$sumsha256 = round(($s9hash + $avalonhash)/1000);

$html = "<head><link href=\"main.css\" type=\"text/css\" rel=\"stylesheet\"/>
<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js\"></script>
<script type=\"text/javascript\" src=\"js/index.js\"></script><title>Summary</title></head>
	<body><p style='font-size: 44px; font-weight: bold; text-align: center; padding-top: 25px'>SUMMARY</p>
	<table id='summarytbl'>
	<tr class=head><td>Miner</td><td>Algo</td><td>All</td><td>Running</td><td>Warming</td><td>Offline</td><td>Hashrate</td><td>Avg. Temp</td><td></td></td></tr>
	<tr>
		<td><a href='s9.php' target='_blank'>S9</a></td>
		<td>SHA256</td>
		<td>$s9all</td>
		<td style='color: #23f223'>$s9on</td>
		<td style='color: yellow'>$s9ssh</td>
		<td style='color: red'>$s9off</td>
		<td>".number_format($summary['S9']['hash']/1000)." Th</td>
		<td>".$summary['S9']['temp']."&deg;C</td>
		<td><a id='s9pass'>Default Password</a></td>
	</tr>
	<tr>
		<td><a href='avalons.php' target='_blank'>Avalon</a></td>
		<td>SHA256</td>
		<td>$avalonall</td>
		<td style='color: #23f223'>$avalonon</td>
		<td>--</td>
		<td style='color: red'>$avalonoff</td>
		<td>".number_format($avalonhash/1000)." Th</td>
		<td>".number_format($avalontemp/$avalonon, 2)."&deg;C</td>
		<td></td>
	</tr>
	<tr>
		<td><a href='l3.php' target='_blank'>L3</a></td>
		<td>Scrypt</td>
		<td>$l3all</td>
		<td style='color: #23f223'>$l3on</td>
		<td style='color: yellow'>$l3ssh</td>
		<td style='color: red'>$l3off</td>
		<td>".number_format($summary['L3']['hash'])." Mh</td>
		<td>".$summary['L3']['temp']."&deg;C</td>
		<td><a id='l3pass'>Default Password</a></td>
	</tr>
	<tr>
		<td><a href='rigs.php' target='_blank'>RIG</a></td>
		<td>ETHash</td>
		<td>$rigall</td>
		<td style='color: #23f223'>$rigon</td>
		<td>--</td>
		<td style='color: red'>$rigoff</td>
		<td>".number_format($summary['rig']['hash'])." Mh</td>
		<td>".$summary['rig']['temp']."&deg;C</td>
		<td></td>
	</tr>
	<tr>
		<td>Total</td>
		<td>SHA256:</td>
		<td>$sumsha256 Th</td>
		<td>Scrypt:</td>
		<td>".number_format($l3hash)." Mh</td>
		<td>ETHash:</td>
		<td>".number_format($summary['rig']['hash'])." Mh</td>
		<td></td>
		<td></td>
	</tr>
	</table>
	<div id='divedit'>
		<div id='editclose'>X</div>
		<div id='editfld'>
			Set Default miners ssh password<br><br>
			Password: <span id='s9pflds'><input id='s9passwd' type=text size=12><input id=s9savepass type=submit value=SAVE></span>
				<span id='l3pflds'><input id='l3passwd' type=text size=12><input id=l3savepass type=submit value=SAVE></span>
		</div>
	</div>
	</body>";

echo $html;
