<?php

error_reporting(E_ERROR | E_PARSE);
$start = microtime(true);

$s9 = json_decode(file_get_contents('json/s9.json'),true);

$s9num = count($s9);
$totalhashrate = 0;

function api($ip,$command) {

	$socket = fsockopen($ip, 4028, $err_code, $err_str, 0.1);
	if (!$socket) {
		$socket2 = fsockopen($ip, 80, $err_code, $err_str, 0.1);
			if ($socket2)	{return 1;}
			else 		{return 0;}
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
	if (!json_decode($buff)) { $json = 0; print "BAD json, error: " . json_last_error();}
	else { $json = json_decode($buff,true);}
	return $json;
}

function miner_details($type,$arr) {

	$id	= $arr['id'];
	$ip	= $arr['ip'];
	$model	= $arr['model'];
	$fan_check= $arr['fanck'];
	$fan_mode= $arr['fanmod'];
	$comment = $arr['comment'];
	$disable = $arr['disabled'];
	$fwfree = '';
	
	if ($disable == 1) {
		return array(0,0,0,"<tr>
		<td><a style=\"color:red;\" href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td style=\"color:red;\">$model</td>
		<td></td>
		<td><span class=\"box badred\">Disabled</span></td>
		<td><span class=\"box badred\"><a href=\"getinfo.php?ip=$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=20></td>
		<td><span class=\"box green\"><a href=\"disable.php?enable=1&id=$id\" target=\"_blank\">E</a></span></td>
		<td><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
		<td>$comment</td></tr>");
	}
	$json = api($ip,'summary+pools+stats');
	if ($json == 0)	{
		return array(0,0,0,"<tr>
		<td><a style=\"color:red;\" href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td style=\"color:red;\">$model</td>
		<td></td>
		<td><span class=\"box red\">Offline</span></td>
		<td><a style=\"color:red;\" href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td colspan=22 align=right><span class=\"box badred\"><a href=\"disable.php?id=$id\" target=\"_blank\">D</a></span><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
		<td>$comment</td></tr>");
	}
	else if ($json == 1) {
		return array(1,0,0,"<tr>
		<td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td style=\"color:yellow;\">$model</td>
		<td></td>
		<td><span class=\"box yellow\">Web</span></td>
		<td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td colspan=21 align=right><a href=\"/command.php?ip=$ip&command=restart\" target=\"_blank\"><img src=\"reboot.png\"></a></td>
		<td><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
		<td>$comment</td></tr>");
        }
	$getworks       = number_format($json['summary'][0]['SUMMARY'][0]['Getworks']);
	$elapsed        = $json['summary'][0]['SUMMARY'][0]['Elapsed'];
	$hw             = $json['summary'][0]['SUMMARY'][0]['Hardware Errors'];
	$accepted       = $json['summary'][0]['SUMMARY'][0]['Accepted'];
	$rejected       = $json['summary'][0]['SUMMARY'][0]['Rejected'];
	$stale          = $json['summary'][0]['SUMMARY'][0]['Stale'];
	$discarded      = $json['summary'][0]['SUMMARY'][0]['Discarded'];
	$ghsav          = $json['summary'][0]['SUMMARY'][0]['GHS av'];
	$ghs5s          = $json['summary'][0]['SUMMARY'][0]['GHS 5s'];
	$blocks         = $json['summary'][0]['SUMMARY'][0]['Found Blocks'];
	
	for ($i=0; $i<6; $i++) {
		$pool_status[$i]   = $json['pools'][0]['POOLS'][$i]['Status'];
		$pool_prio[$i]     = $json['pools'][0]['POOLS'][$i]['Priority'];
		$pool_url[$i]      = $json['pools'][0]['POOLS'][$i]['URL'];
		$pool_user[$i]     = $json['pools'][0]['POOLS'][$i]['User'];
        $pool_diff[$i]     = $json['pools'][0]['POOLS'][$i]['Diff'];
		$pool_lstime[$i]   = $json['pools'][0]['POOLS'][$i]['Last Share Time'];
		if (strlen($pool_user[$i])>15) {
			$workerparts = explode(".", $pool_user[$i]);
			$pool_user[$i] = substr($workerparts[0], 0, -4) . "...".$workerparts[1];
		}
	}

	$fw_type	= $json['stats'][0]['STATS'][0]['Type'];
	$miner_ver      = $json['stats'][0]['STATS'][0]['Miner'];
	$miner_compile  = $json['stats'][0]['STATS'][0]['CompileTime'];
	$bmminer_ver    = $json['stats'][0]['STATS'][0]['BMMiner'];
	$freq           = $json['stats'][0]['STATS'][1]['frequency'];
	
	for ($x=0;$x<3;$x++) {
		if ($fw_type == 'Antminer S9k') {
			$y = 1 + $x;
            $asic_ctemp[$x]  = $json['stats'][0]['STATS'][1]["temp$y"];
            $asic_btemp[$x]  = $json['stats'][0]['STATS'][1]["temp2_$y"];
            $asic_freq[$x]   = $json['stats'][0]['STATS'][1]["freq$y"];
		}
		else {
            $y = 6 + $x;
            $asic_ctemp[$x]  = $json['stats'][0]['STATS'][1]["temp2_$y"];
            $asic_btemp[$x]  = $json['stats'][0]['STATS'][1]["temp$y"];
            $asic_freq[$x]   = $json['stats'][0]['STATS'][1]["freq_avg$y"];
		}
        $asic_volt[$x]   = $json['stats'][0]['STATS'][1]["voltage$y"];
        $asic_chips[$x]  = $json['stats'][0]['STATS'][1]["chain_acn$y"];
        $asic_power[$x]  = $json['stats'][0]['STATS'][1]["chain_consumption$y"];
        if (!$asic_volt[$x]) {$asic_volt[$x] = $json['stats'][0]['STATS'][1]["chain_vol$y"]/100;}
        if 	($asic_btemp[$x]>89) { $bcl[$x] = 'red';}
        else if ($asic_btemp[$x]>80) { $bcl[$x] = 'orange';}
        else if ($asic_btemp[$x]>75) { $bcl[$x] = 'yellow';}
        else if ($asic_btemp[$x]>50) { $bcl[$x] = 'green';}
        else if ($asic_btemp[$x]>15) { $bcl[$x] = 'blue';}
        else 			     { $bcl[$x] = 'badred';}
        if	($asic_ctemp[$x]>99) { $ccl[$x] = 'red';}
        else if ($asic_ctemp[$x]>89) { $ccl[$x] = 'orange';}
        else if ($asic_ctemp[$x]>85) { $ccl[$x] = 'yellow';}
        else if ($asic_ctemp[$x]>79) { $ccl[$x] = 'greenlight';}
        else if ($asic_ctemp[$x]>59) { $ccl[$x] = 'green';}
        else if ($asic_ctemp[$x]>15) { $ccl[$x] = 'blue';}
        else			{ $ccl[$x] = 'badred';}
	}
	if ($fw_type == 'Antminer S9k') {
        $fan1    = $json['stats'][0]['STATS'][1]['fan1'];
        $fan2    = $json['stats'][0]['STATS'][1]['fan2'];
        $voltavg = $json['stats'][0]['STATS'][1]['Voltage'];
	}
	else {
        $fan1    = $json['stats'][0]['STATS'][1]['fan5'];
        $fan2    = $json['stats'][0]['STATS'][1]['fan6'];
        $fan3    = $json['stats'][0]['STATS'][1]['fan3'];
        $voltavg = round(array_sum($asic_volt)/3,2);
	}
	$hrate_ideal	= $json['stats'][0]['STATS'][1]['total_rateideal'];
	$temp_num       = $json['stats'][0]['STATS'][1]['temp_num'];

	$asic_chip_sum = array_sum($asic_chips);
	$freqavg = round(array_sum($asic_freq)/3);
	if ($voltavg==0) {$voltavg ='';}
	if ($ghs5s > 99000) { $ghs5s = 16000;}
	if	(preg_match('/braiins/', $fw_type)) { $fw_type = 'Brains';}
	else if	(preg_match('/Antminer S9k/', $fw_type)) { $fw_type = 'S9K';}
	else if (preg_match('/vnish (.*)\)/', $fw_type, $vers)) {$fw_type = "ADIP"; }
	else if (preg_match('/Antminer/', $fw_type) && $bmminer_ver == '2.0.0 rwglr') {	$fw_type = 'MSK';	}
	if	($ghs5s>16000) {$thcl = 'highfiol';}
	else if	($ghs5s>15000) {$thcl = 'fiol';}
	else if ($ghs5s>14500) {$thcl = 'greenlight';}
	else if ($ghs5s>14000) {$thcl = 'green';}
	else if ($ghs5s>13450) {$thcl = 'blue';}
	else if ($ghs5s>13000) {$thcl = 'yellow';}
	else if ($ghs5s>10000) {$thcl = 'orange';}
	else {$thcl = 'red';}
	if ($asic_chip_sum<189 && $fw_type != 'S9K') {$csumcl = 'red';}
	else if ($asic_chip_sum<180 && $fw_type == 'S9K') {$csumcl = 'red';}
	else {$csumcl = '';}
	
	$total_power	= array_sum($asic_power);
	if	($elapsed<180) 		{	$uptime = $elapsed . " sec";$upbox ='box red';}
	else if ($elapsed<3600*2)	{	$uptime = floor($elapsed/60) . " min";$upbox ='box yellow'; }
	else if ($elapsed<3600*48)	{	$uptime = floor($elapsed/3600) . " H";	$upbox =' box blue';}
	else 				{	$uptime = floor($elapsed/(3600*24)) . " days";	}
	
	if ($hw > 100000) {	$hwcol = 'hwred'; }
	if ($fan1 == 0) { $fan1 = $fan3; }
	$rejrate = round((100*($rejected/$accepted)), 3);
	for ($i=0;$i<6;$i++){
		if ($pool_status[$i] == 'Alive' && $pool_prio[$i] == 0) {$poolnum = $i; break;}
	}
	$pool_url[$poolnum] = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool_url[$poolnum]);
	if (preg_match('/bOS/', $miner_ver)) { $miner_ver = preg_replace("/bOS_am1-s9-(.*?)-.*/","\$1",$miner_ver); }
	if (preg_match('/kano.is/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Kano'; }
	else if (preg_match('/antpool/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Antpool'; }
	else if (preg_match('/viabtc.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'ViaBTC'; }
	else if (preg_match('/f2pool.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'F2Pool'; }
	else if (preg_match('/slushpool.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Slush'; }
	else if (preg_match('/emcd.io/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'EMCD'; }
	else if (preg_match('/hashcryptos.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Hashcryptos'; }
	else if (preg_match('/sigmapool.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Sigma'; }
	else if (preg_match('/litecoinpool.org/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Litecoinpool'; }
	$fan1cl = $fan2cl = 'green';
	if ($fan1>5999) { $fan1cl = 'red'; }
	else if ($fan1>5000) { $fan1cl = 'yellow'; }
	else if ($fan1==0) { $fan1cl = 'badred'; }
	else if ($fan1<3500) { $fan1cl = 'blue'; }
	if ($fan2>5999) { $fan2cl = 'red'; }
	else if ($fan2>5000) { $fan2cl = 'yellow'; }
	else if ($fan2==0) { $fan2cl = 'badred'; }
	else if ($fan2<3500) { $fan2cl = 'blue'; }
	$thdiff = $hrate_ideal - $ghsav;
	if ($thdiff<0)  {$thavcol = 'fgreen';}
	else if ($thdiff>1000)   {$thavcol = 'fred';}
	else if ($thdiff>400)   {$thavcol = 'forange';}
	else if ($thdiff>150)   {$thavcol = 'fyellow';}
	else    {$thavcol = 'fblue';}
	$pwcol = 'fiol';
	if ($total_power == 0) { 
		$pwcol = '';
        if ($ghsav>16000)       { $total_power = 1550;}
        else if ($ghsav>15500)  { $total_power = 1500;}
        else if ($ghsav>15000)  { $total_power = 1450;}
        else if ($ghsav>14500)  { $total_power = 1400;}
        else if ($ghsav>14000)  { $total_power = 1300;}
        else if ($ghsav>13500)  { $total_power = 1270;}
        else if ($ghsav>13000)  { $total_power = 1250;}
        else if ($ghsav>12500)  { $total_power = 1200;}
        else if ($ghsav>600)  { $total_power = 1000;}
        else if ($ghsav>12000)  { $total_power = 1170;}
        else if ($ghsav>11500)  { $total_power = 1150;}
        else if ($ghsav>11000)  { $total_power = 1100;}
        else if ($ghsav>4000 and $ghsav<11000) { $total_power = 1000;}
        else if ($ghsav>650)  { $total_power = 1000;}
        else if ($ghsav>600)  { $total_power = 900;}
        else if ($ghsav>575)  { $total_power = 870;}
        else if ($ghsav>550)  { $total_power = 850;}
        else if ($ghsav>520)  { $total_power = 825;}
        else if ($ghsav>510)  { $total_power = 800;}
        else if ($ghsav>500)  { $total_power = 780;}
        else   			{ $total_power = 750;}
        }
	
	if ($asic_ctemp[0] < 16) {
		if 	($asic_ctemp[1] < 16) {$ctempavg = $asic_ctemp[2];}
		else if ($asic_ctemp[2] < 16) {$ctempavg = $asic_ctemp[1];}
		else	{$ctempavg = array_sum($asic_ctemp)/2;}
	}
	else if ($asic_ctemp[1] < 16) {
		if 	($asic_ctemp[0] < 16) {$ctempavg = $asic_ctemp[2];}
		else if ($asic_ctemp[2] < 16) {$ctempavg = $asic_ctemp[0];}
		else	{$ctempavg = array_sum($asic_ctemp)/2;}
	}
	else if ($asic_ctemp[2] < 16) {
		if 	($asic_ctemp[0] < 16) {$ctempavg = $asic_ctemp[1];}
		else if ($asic_ctemp[1] < 16) {$ctempavg = $asic_ctemp[0];}
		else	{$ctempavg = array_sum($asic_ctemp)/2;}
	}
	else {
		if ($type == 's9')	{ $ctempavg = array_sum($asic_ctemp)/3;}
		else 			{ $ctempavg = array_sum($asic_ctemp)/4;}
	}

	if ($ctempavg>99) { $tacol = 'red';}
	else if ($ctempavg>89) { $tacol = 'orange';}
	else if ($ctempavg>85) { $tacol = 'yellow';}
	else if ($ctempavg>79) { $tacol = 'greenlight';}
	else if ($ctempavg>60) { $tacol = 'green';}
	else { $tacol = 'blue';}
	
	for ($j=0; $j<3;$j++) {
		if ($asic_ctemp[$j]==0) {$add[$j] = 'style="padding-left: 12px;"';}
		else		{$add[$j] = '';}
		if ($asic_btemp[$j]==0) {$badd[$j] = 'style="padding-left: 12px;"';}
		else		{$badd[$j] = '';}
	}
	if	($freqavg > 724)	{ $freqcol = 'ffiol';}
	else if ($freqavg > 699)	{ $freqcol = 'fgreen';}
	else if ($freqavg > 634)	{ $freqcol = 'fblue';}
	else if ($freqavg > 549)	{ $freqcol = 'fyellow';}
	else if ($fw_type == 'S9K')	{ $freqcol = '';}
	else				{ $freqcol = 'fred';}
	
	if ($fan_mode == 'a') { $fanmodecl = 'fiol';}
	else if ($fan_mode == 'off') { $fanmodecl = 'red';}
	else  	 { $fanmodecl = 'badred';}
	if ($fan_check == 'on')	{ $fanckcl = 'green';}
	else		{ $fanckcl = 'badred';}

	$lst = explode(':',$pool_lstime[$poolnum]);
	if ($lst[0]>0 or $lst[1]>3) { $lscol = 'hwred';}
	else if ($lst[1] > 0) { $lscol = 'fyellow';}
	if ($pool_status[$i] == 'Alive') {$poolcol[$i] = 'fgreen';}
	else {$poolcol[$i] = 'fred';}

	$html = "<tr>
		<td><a href=\"getinfo.php?id=$id&type=$type\" target=\"_blank\">$id</a></td>
		<td>$model</td>
		<td>$fw_type</td>
		<td>$miner_ver</td>
		<td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td><span class=\"$poolcol[$poolnum]\">$pool_url[$poolnum]</span></td>
		<td>$pool_user[$poolnum]</td>
		<td>$pool_diff[$poolnum]</td>
		<td>$getworks</td>
		<td class=$lscol>$pool_lstime[$poolnum]</td>
		<td>$blocks</td>
		<td><span class=\"$upbox\">$uptime</span></td>
		<td>
		    <span class=\"box $fanckcl\">$fan_check</span>
		    <span class=\"box $fanmodecl\">$fan_mode</span>
		    <span class=\"box $fan1cl\">$fan1</span>
		    <span class=\"box $fan2cl\">$fan2</span>
		</td>
		<td>$voltavg</td>
		<td class=$freqcol>$freqavg</td>
		<td>".number_format($hrate_ideal)."</td>
		<td><span class=\"box $thcl\">" . number_format($ghs5s). "</span></td>
		<td class=\"ghsav $thavcol\">".number_format($ghsav)."</td>
		<td class=$hwcol>". number_format($hw) ."</td>
		<td align=right>$rejrate%</td>
		<td><span class=\"box $tacol\">".round($ctempavg) ."&deg;</span></td><td>";
		for ($j=0; $j<3;$j++) { $html .= "<span $badd[$j] class=\"box $bcl[$j]\">$asic_btemp[$j]</span>";}
		$html .= "</td><td>";
		for ($j=0; $j<3;$j++) { $html .= "<span $add[$j] class=\"box $ccl[$j]\">$asic_ctemp[$j]</span>";}
		$html .= "</td><td><span class=\"box $csumcl\">$asic_chip_sum</span></td>
	<td><span class=\"box $pwcol\">". number_format($total_power) ."</span></td>
	<td align=center><a href=\"command.php?ip=$ip&command=restart\" target=\"_blank\"><img src=\"reboot.png\"></a></td>
	<td><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
	<td>$comment</td></tr>";

	return array(2,$ghs5s,$ghsav,$html,$total_power,$ctempavg,$pool_url[$poolnum],$rejrate);
}

$html = '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>S9</title></head>
	<body><table border=0 cellspacing=0 cellpadding=4><tr class=head>
	<td>ID</td>
	<td>Model</td>
	<td>FW</td>
	<td>Miner</td>
	<td>IP</td>
	<td>Pool</td>
	<td>Worker</td>
	<td>Diff</td>
	<td>Works</td>
	<td>LS time</td>
	<td>B</td>
	<td>Uptime</td>
	<td>Fans</td><td>V</td><td>Freq</td>
	<td>TH ideal</td><td>TH 5s</td><td>TH Avg</td><td>HW</td><td>Reject</td>
	<td>T(avg)</td><td>Board Temp</td><td>Chip Temp</td>
	<td>Chips</td><td>Watt</td><td colspan=2>Action</td><td>Comment</td></tr>';

$totalavg = 0;
$total_pwr = 0;
$total_temp = 0;
$online = 0;
$offline = 0;
$avail = 0;
$s9numt = $s9num;
$rejrateall = 0;

foreach($s9 as $x) {

	$vars = miner_details('s9',$x);
	$state = $vars[0];
	$totalhashrate += $vars[1];
	$totalavg += $vars[2];
	$html .= $vars[3];
	$total_pwr += $vars[4];
	$total_temp += $vars[5];
	$poolname = $vars[6];
	$rejrateall += $vars[7];
	if ($vars[5] < 10) {
		$s9numt -= 1;
	}
	if	($state == 0) {$offline++;}
	else if ($state == 1) {$avail++;}
	else if ($state == 2) {$online++;}

}

if ($avail>0) {$availclass = 'fyellow';} else {$availclass = '';}
if ($offline>0) {$offclass = 'fred';} else { $offclass = '';}

$html .= "<tr><td colspan=2><span class=\"box fiol\"><a href=\"add.php?type=s9\" target=\"_blank\">ADD</a></span></td></tr></table><br>
		<span class=bold>Total: ". $s9num . " miners (
		<span class=fgreen>$online</span> / 
		<span class=$availclass>$avail</span> / 
		<span class=$offclass>$offline</span>)" . " | 
		<span class=fblue>". number_format($totalavg/$online/1000,3)."</span> T (S9avg) | " . number_format($totalhashrate/1000,1) . " T | 
		<span class=fblue>".number_format($totalavg/1000,1) ."</span> T(avg) | ".round($total_temp/$s9numt,2)."&deg; (avg) | 
		<span class=forange>" .number_format($total_pwr/1000,2)."</span> kWt | Reject Rate: ". round($rejrateall/$s9numt,3) ."% || Load time: " . round(microtime(true) - $start, 3) . " sec (" . date('Y-m-d H:i:s') .")<br>";

echo $html . '</body>';
