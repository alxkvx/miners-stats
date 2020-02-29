<?php

error_reporting(E_ERROR | E_PARSE);
$start = microtime(true);

$l3 = json_decode(file_get_contents('json/l3.json'),true);

$l3num = count($l3);
$l3hashrate = 0;

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
	if (!json_decode($buff)) { print "BAD json, error: " . json_last_error();}
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
		<td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td><span class=\"box badred\">Disabled</span></td>
		<td></td><td></td>
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
		<td>$model</td>
		<td><span class=\"box red\">Offline</span></td>
		<td></td>
		<td><a style=\"color:red;\" href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td colspan=21 align=right><span class=\"box badred\"><a href=\"disable.php?id=$id\" target=\"_blank\">D</a></span><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
		<td>$comment</td></tr>");
	}
	else if ($json == 1) {
		return array(1,0,0,"<tr>
		<td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td>$model</td>
		<td><span class=\"box yellow\">Web</span></td>
		<td></td>
		<td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td colspan=20 align=right><a href=\"/command.php?ip=$ip&command=restart\" target=\"_blank\"><img src=\"reboot.png\"></a></td>
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
	}

	$fw_type	= $json['stats'][0]['STATS'][0]['Type'];
	$miner_ver      = $json['stats'][0]['STATS'][0]['Miner'];
	$miner_compile  = $json['stats'][0]['STATS'][0]['CompileTime'];
	$bmminer_ver    = $json['stats'][0]['STATS'][0]['BMMiner'];
	$freq           = $json['stats'][0]['STATS'][1]['frequency'];
	
		for ($x=0;$x<4;$x++) {
			$y = 1 + $x;
                        $asic_btemp[$x] = $json['stats'][0]['STATS'][1]["temp$y"];
                        $asic_ctemp[$x] = $json['stats'][0]['STATS'][1]["temp2_$y"];
                        $asic_chips[$x] = $json['stats'][0]['STATS'][1]["chain_acn$y"];
			$asic_freq[$x]  = $json['stats'][0]['STATS'][1]["frequency$y"];
			$asic_power[$x]  = $json['stats'][0]['STATS'][1]["watt$y"];
			if 	($asic_btemp[$x]>80) { $bcl[$x] = 'red';}
			else if ($asic_btemp[$x]>70) { $bcl[$x] = 'orange';}
			else if ($asic_btemp[$x]>65) { $bcl[$x] = 'yellow';}
			else if ($asic_btemp[$x]>50) { $bcl[$x] = 'green';}
			else 				{ $bcl[$x] = 'blue';}
			if 	($asic_ctemp[$x]>79) { $ccl[$x] = 'red';}
			else if ($asic_ctemp[$x]>69) { $ccl[$x] = 'orange';}
			else if ($asic_ctemp[$x]>65) { $ccl[$x] = 'yellow';}
			else if ($asic_ctemp[$x]>60) { $ccl[$x] = 'greenlight';}
			else if ($asic_ctemp[$x]>50) { $ccl[$x] = 'green';}
			else if ($asic_ctemp[$x]>15) { $ccl[$x] = 'blue';}
			else 			{ $ccl[$x] = 'fiol';}
		}
		$fan1           = $json['stats'][0]['STATS'][1]['fan1'];
		$fan2           = $json['stats'][0]['STATS'][1]['fan2'];
		if (preg_match('/Antminer L3\+(.*) (v.*)/', $fw_type, $lvers)) {$fw_type = $lvers[1]; }

		$freqavg = round(array_sum($asic_freq)/4);
		if ($freqavg<1) {$freqavg = $freq;}
		$asic_chip_sum = array_sum($asic_chips);
		if ($ghs5s>650) {$thcl = 'greenlight';}
		else if ($ghs5s>600) {$thcl = 'green';} 
		else if ($ghs5s>500) {$thcl = 'blue';}
		else if ($ghs5s>450) {$thcl = 'yellow';} else if ($ghs5s>400) {$thcl = 'orange';} else {$thcl = 'red';}
		if ($asic_chip_sum<288) {$csumcl = 'red';} else {$csumcl = '';}
	
	$total_power	= array_sum($asic_power);
	if	($elapsed<180) 		{	$uptime = $elapsed . " sec";$upbox ='box red';}
	else if ($elapsed<3600*2)	{	$uptime = floor($elapsed/60) . " min";$upbox ='box yellow'; }
	else if ($elapsed<3600*48)	{	$uptime = floor($elapsed/3600) . " H";	$upbox =' box blue';}
	else 				{	$uptime = floor($elapsed/(3600*24)) . " days";	}
	
	if ($hw > 100000) {	$hwcol = 'hwred'; }
	if ($fan1 == 0) { $fan1 = $fan3; }
	$rejrate = round((100*($rejected/$accepted)), 3);
	for ($i=0;$i<6;$i++){
		if ($pool_prio[$i] == 0 ) {$poolnum = $i; break;}
	}
	$pool_url[$poolnum] = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool_url[$poolnum]);
	if (preg_match('/slushpool.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Slush'; }
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
                else if ($ghsav>600)  { $total_power = 1000;}
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
		$ctempavg = array_sum($asic_ctemp)/4;
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
	if	($freqavg > 450)	{ $freqcol = 'ffiol';}
	else if ($freqavg > 400)	{ $freqcol = 'fgreen';}
	else if ($freqavg > 375)	{ $freqcol = 'fblue';}
	else if ($freqavg > 349)	{ $freqcol = 'fyellow';}
	else				{ $freqcol = 'fred';}
	
	if ($fan_mode == 'a') { $fanmodecl = 'fiol';}
	else if ($fan_mode == 'off') { $fanmodecl = 'red';}
	else  	 { $fanmodecl = 'badred';}
	if ($fan_check == 'on')	{ $fanckcl = 'green';}
	else		{ $fanckcl = 'badred';}

	$lst = explode(':',$pool_lstime[$poolnum]);
	if ($lst[0]>0 or $lst[1]>0 or $lst[2]>59) { $lscol = 'hwred';}
	else if ($lst[2] > 20) { $lscol = 'fyellow';}
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
		<td><span class=\"box $fanckcl\">$fan_check</span><span class=\"box $fanmodecl\">$fan_mode</span><span class=\"box $fan1cl\">$fan1</span><span class=\"box $fan2cl\">$fan2</span></td>
		<td>$voltavg</td>
		<td class=$freqcol>$freqavg</td>
		<td><span class=\"box $thcl\">" . number_format($ghs5s). "</span></td>
		<td class=\"ghsav $thavcol\">".number_format($ghsav)."</td>
		<td class=$hwcol>". number_format($hw) ."</td>
		<td align=right>$rejrate%</td>
		<td><span class=\"box $tacol\">".round($ctempavg) ."&deg;</span></td>
        	<td><span class=\"box $bcl[0]\"> $asic_btemp[0]</span><span class=\"box $bcl[1]\"> $asic_btemp[1]</span><span class=\"box $bcl[2]\">$asic_btemp[2]</span><span class=\"box $bcl[3]\">$asic_btemp[3]</span></td>
        	<td><span class=\"box $ccl[0]\">$asic_ctemp[0]</span><span class=\"box $ccl[1]\">$asic_ctemp[1]</span><span class=\"box $ccl[2]\">$asic_ctemp[2]</span><span class=\"box $ccl[3]\">$asic_ctemp[3]</span></td>
		<td><span class=\"box $csumcl\">$asic_chip_sum</span></td>
		<td><span class=\"box $pwcol\">". number_format($total_power) ."</span></td>
		<td align=center><a href=\"command.php?ip=$ip&command=restart\" target=\"_blank\"><img src=\"reboot.png\"></a></td>
		<td><span class=\"box red\"><a href=\"del.php?id=$id\" target=\"_blank\">X</a></span></td>
		<td>$comment</td></tr>";

	return array(2,$ghs5s,$ghsav,$html,$total_power,$ctempavg,$pool_url[$poolnum],$rejrate);
}

$html = '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>L3</title></head><body>';

$html .= "<table border=0 cellspacing=0 cellpadding=4><tr class=head>
	<td>ID</td>
	<td>Model</td>
	<td>Fw</td>
	<td>Vers</td>
	<td>IP</td>
	<td>Pool</td>
	<td>Worker</td>
	<td>Diff</td>
	<td>Works</td>
	<td>LS time</td>
	<td>Block</td><td>Uptime</td><td>Fans</td><td>V</td><td>Freq</td><td>MH 5s</td><td>MH Avg</td><td>HW</td><td>Reject</td><td>T(avg)</td><td>Board Temp</td><td>Chip Temp</td><td>Chips</td><td>Watt</td><td colspan=2>Action</td><td>Comment</td></tr>";

$total_lpwr=0;
$l3hashavg =0;
$total_temp = 0;
$l3numt = $l3num;
$online = 0;
$offline = 0;
$avail = 0;
$rejrateall = 0;

foreach($l3 as $x) {
	$vars = miner_details('l3',$x);
	$state = $vars[0];
    $l3hashrate += $vars[1];
    $l3hashavg += $vars[2];
    $html .= $vars[3];
	$total_lpwr += $vars[4];
	$total_temp += $vars[5];
	$rejrateall += $vars[7];
	if ($vars[5] < 10) {  $l3numt -= 1; }
	if	($state == 0) {$offline++;}
	else if ($state == 1) {$avail++;}
	else if ($state == 2) {$online++;}

}

$html .= "<tr><td colspan=2><span class=\"box fiol\"><a href=\"add.php?type=l3\" target=\"_blank\">ADD</a></span></td></tr></table>
<br><span class=bold>Total: ". $l3num . " miners (<span class=fgreen>$online</span> / <span class=fyellow>$avail</span> / <span class=fred>$offline</span>)" . " | " . number_format($l3hashrate) . " Mh | ".number_format($l3hashavg). " Mh(Avg) | ".round($total_temp/$l3numt,2)."&deg; (avg) | <span class=forange>" .number_format($total_lpwr/1000,2)."</span> kWt | Reject Rate: ". round($rejrateall/$l3numt,3) ."%</span> || Load time: " . round(microtime(true) - $start, 3) . " sec (" . date('Y-m-d H:i:s') .")</span>";

print $html . "</body>";

?>
