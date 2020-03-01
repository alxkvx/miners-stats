<?php

error_reporting(E_ERROR | E_PARSE);
$start = microtime(true);

$s9 = json_decode(file_get_contents('json/s9.json'),true);
$l3 = json_decode(file_get_contents('json/l3.json'),true);

$s9num = count($s9);
$l3num = count($l3);

$totalhashrate = 0;
$l3hashrate = 0;

function api($ip,$command) {

	$socket = fsockopen($ip, 4028, $err_code, $err_str, 0.2);
	if (!$socket) {
		$socket2 = fsockopen($ip, 22, $err_code, $err_str, 0.2);
			if ($socket2)	{return 1;}
			else 		{return 0;}
	}
	$data = '{"id":1,"jsonrpc":"2.0","command": "'. $command . '"}' . "\r\n\r\n";
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fgets($socket, 4028); }
	if ($socket) {  fclose($socket); }
	$buff = substr($buffer,0,strlen($buffer)-1);
	$buff = preg_replace('/}{/','},{',$buff);
	$buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
#       print $buff . '<br><br>';
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
	$disable = $arr['disabled'];
	$fwfree = '';
	
	if ($disable == 1) {
		return array(0,0,0,"<tr>
		<td></td>
		<td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td><span class=\"box badred\">Disabled</span></td>
		<td></td>
		<td><span class=\"box badred\"><a href=\"getinfo.php?ip=$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=21></td></tr>");
	}
	$json = api($ip,'summary+pools+stats');
	if ($json == 0)	{
		return array(0,0,0,"<tr>
		<td></td>
		<td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td>
		<td><span class=\"box red\">Offline</span></td>
		<td></td>
		<td><span class=\"box red\"><a href=\"http://$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=21></td></tr>");
	}
	else if ($json == 1) {
                return array(1,0,0,"<tr><td><span class=\"box yellow\">SSH</span></td><td></td><td></td><td></td><td><a href=\"getinfo.php?id=$id\" target=\"_blank\">$id</a></td><td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td><td colspan=21 align=right><a href=\"command.php?ip=$ip&command=restart\" target=\"_blank\"><img src=\"reboot.png\"></a></td></tr>");
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
		$pool_works[$i]    = $json['pools'][0]['POOLS'][$i]['Getworks'];
		$pool_lstime[$i]   = $json['pools'][0]['POOLS'][$i]['Last Share Time'];
	}

	$fw_type	= $json['stats'][0]['STATS'][0]['Type'];
	$miner_ver      = $json['stats'][0]['STATS'][0]['Miner'];
	$miner_compile  = $json['stats'][0]['STATS'][0]['CompileTime'];
	$bmminer_ver    = $json['stats'][0]['STATS'][0]['BMMiner'];
	$freq           = $json['stats'][0]['STATS'][1]['frequency'];
	
	if ($type == 's9') {
		for ($x=0;$x<3;$x++) {
			$y = 6 + $x;
			$asic_btemp[$x]  = $json['stats'][0]['STATS'][1]["temp$y"];
			$asic_volt[$x]   = $json['stats'][0]['STATS'][1]["voltage$y"];
			$ctemp[$x]       = $json['stats'][0]['STATS'][1]["temp2_$y"];
			$asic_chips[$x]  = $json['stats'][0]['STATS'][1]["chain_acn$y"];
			$asic_freq[$x]   = $json['stats'][0]['STATS'][1]["freq_avg$y"];
			$asic_power[$x]  = $json['stats'][0]['STATS'][1]["chain_consumption$y"];
                        if (!$asic_volt[$x]) {$asic_volt[$x] = $json['stats'][0]['STATS'][1]["chain_vol$y"]/100;}
			if 	($asic_btemp[$x]>89) { $bcl[$x] = 'red';}
			else if ($asic_btemp[$x]>80) { $bcl[$x] = 'orange';}
			else if ($asic_btemp[$x]>75) { $bcl[$x] = 'yellow';}
			else if ($asic_btemp[$x]>50) { $bcl[$x] = 'green';}
			else if ($asic_btemp[$x]>15) { $bcl[$x] = 'blue';}
			else 			     { $bcl[$x] = 'badred';}
			if	($ctemp[$x]>99) { $ccl[$x] = 'red';}
			else if ($ctemp[$x]>89) { $ccl[$x] = 'orange';}
			else if ($ctemp[$x]>85) { $ccl[$x] = 'yellow';}
			else if ($ctemp[$x]>79) { $ccl[$x] = 'greenlight';}
			else if ($ctemp[$x]>59) { $ccl[$x] = 'green';}
			else if ($ctemp[$x]>15) { $ccl[$x] = 'blue';}
			else			{ $ccl[$x] = 'badred';}
		}
		$fan1           = $json['stats'][0]['STATS'][1]['fan5'];
		$fan2           = $json['stats'][0]['STATS'][1]['fan6'];
		$fan3           = $json['stats'][0]['STATS'][1]['fan3'];
		$hrate_ideal	= $json['stats'][0]['STATS'][1]['total_rateideal'];
		$temp_num       = $json['stats'][0]['STATS'][1]['temp_num'];

		$total_power	= array_sum($asic_power);
		$asic_chip_sum = array_sum($asic_chips);
		$freqavg = round(array_sum($asic_freq)/3);
		$voltavg = round(array_sum($asic_volt)/3,2);
		if ($voltavg==0) {$voltavg ='';}
		if ($ghs5s > 99000) { $ghs5s = 16000;}	
		if	(preg_match('/braiins/', $fw_type)) { $fw_type = 'Brains';}
		else if	(preg_match('/free 3/', $fw_type)) { $fw_type = 'Staiki'; $fw_ver = '3.6.8'; }
		else if (preg_match('/vnish (.*)\)/', $fw_type, $vers)) {$fw_type = "ADIP"; $fw_ver = $vers[1];}
		else if (preg_match('/Antminer/', $fw_type) && $bmminer_ver == '2.0.0 rwglr') {
			$fw_type = 'MSK';
			preg_match('/\w+ (\w+ \d+) .*/', $miner_compile, $cdate);
			$fw_ver = $cdate[1];
		}
		if ($fw_ver == '3.6.8') {$fwfree = 'class="box fiol"';}
		if	($ghs5s>16000) {$thcl = 'highfiol';}
		else if	($ghs5s>15000) {$thcl = 'fiol';}
		else if ($ghs5s>14500) {$thcl = 'greenlight';}
		else if ($ghs5s>14000) {$thcl = 'green';}
		else if ($ghs5s>13450) {$thcl = 'blue';}
		else if ($ghs5s>13000) {$thcl = 'yellow';}
		else if ($ghs5s>10000) {$thcl = 'orange';}
		else {$thcl = 'red';}
		if ($asic_chip_sum<189) {$csumcl = 'red';} else {$csumcl = '';}
	}
	else {
		for ($x=0;$x<4;$x++) { $y = 1 + $x;
                        $asic_btemp[$x] = $json['stats'][0]['STATS'][1]["temp$y"];
                        $ctemp[$x]      = $json['stats'][0]['STATS'][1]["temp2_$y"];
                        $asic_chips[$x] = $json['stats'][0]['STATS'][1]["chain_acn$y"];
			$asic_freq[$x]  = $json['stats'][0]['STATS'][1]["frequency$y"];
			if 	($asic_btemp[$x]>80) { $bcl[$x] = 'red';}
			else if ($asic_btemp[$x]>70) { $bcl[$x] = 'orange';}
			else if ($asic_btemp[$x]>65) { $bcl[$x] = 'yellow';}
			else if ($asic_btemp[$x]>50) { $bcl[$x] = 'green';}
			else 				{ $bcl[$x] = 'blue';}
			if 	($ctemp[$x]>79) { $ccl[$x] = 'red';}
			else if ($ctemp[$x]>69) { $ccl[$x] = 'orange';}
			else if ($ctemp[$x]>65) { $ccl[$x] = 'yellow';}
			else if ($ctemp[$x]>60) { $ccl[$x] = 'greenlight';}
			else if ($ctemp[$x]>50) { $ccl[$x] = 'green';}
			else if ($ctemp[$x]>15) { $ccl[$x] = 'blue';}
			else 			{ $ccl[$x] = 'fiol';}
		}
		$fan1           = $json['stats'][0]['STATS'][1]['fan1'];
		$fan2           = $json['stats'][0]['STATS'][1]['fan2'];
		if (preg_match('/Antminer L3\+(.*) (v.*)/', $fw_type, $lvers)) {$fw_type = $lvers[1]; $fw_ver = $lvers[2];}

		$freqavg = round(array_sum($asic_freq)/4);
		if ($freqavg<1) {$freqavg = $freq;}
		$asic_chip_sum = array_sum($asic_chips);
		if ($ghs5s>650) {$thcl = 'greenlight';}
		else if ($ghs5s>600) {$thcl = 'green';} 
		else if ($ghs5s>500) {$thcl = 'blue';}
		else if ($ghs5s>450) {$thcl = 'yellow';} else if ($ghs5s>400) {$thcl = 'orange';} else {$thcl = 'red';}
		if ($asic_chip_sum<288) {$csumcl = 'red';} else {$csumcl = '';}
	}
	
	if	($elapsed<180) 		{	$uptime = $elapsed . " sec";$upbox ='box red';}
	else if ($elapsed<3600*2)	{	$uptime = floor($elapsed/60) . " min";$upbox ='box yellow'; }
	else if ($elapsed<3600*48)	{	$uptime = floor($elapsed/3600) . " H";	$upbox =' box blue';}
	else 				{	$uptime = floor($elapsed/(3600*24)) . " days";	}
	
	if ($hw > 100000) {	$hw = '<td class="hwred">' . number_format($hw);}
	else 			{	$hw = "<td>" . number_format($hw);	}
	if ($fan1 == 0) { $fan1 = $fan3; }
	$rejrate = round((100*($rejected/$accepted)), 3);
	for ($i=0;$i<6;$i++){
		if ($pool_prio[$i] == 0 ) {$poolnum = $i; break;}
	}
	$pool_url[$poolnum] = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool_url[$poolnum]);
	if (preg_match('/bOS/', $miner_ver)) { $miner_ver = preg_replace("/bOS_am1-s9-(.*?)-.*/","\$1",$miner_ver); }
	if (preg_match('/kano.is/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Kano'; }
	#else if (preg_match('/viabtc.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'ViaBTC'; }
	else if (preg_match('/slushpool.com/', $pool_url[$poolnum])) { $pool_url[$poolnum] = 'Slush'; }
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
	
	if ($ctemp[0] < 16) {
		if 	($ctemp[1] < 16) {$ctempavg = $ctemp[2];}
		else if ($ctemp[2] < 16) {$ctempavg = $ctemp[1];}
		else	{$ctempavg = ($ctemp[1] + $ctemp[2])/2;}
	}
	else if ($ctemp[1] < 16) {
		if 	($ctemp[0] < 16) {$ctempavg = $ctemp[2];}
		else if ($ctemp[2] < 16) {$ctempavg = $ctemp[0];}
		else	{$ctempavg = ($ctemp[0] + $ctemp[2])/2;}
	}
	else if ($ctemp[2] < 16) {
		if 	($ctemp[0] < 16) {$ctempavg = $ctemp[1];}
		else if ($ctemp[1] < 16) {$ctempavg = $ctemp[0];}
		else	{$ctempavg = ($ctemp[0] + $ctemp[1])/2;}
	}
	else {
		if ($type == 's9')	{ $ctempavg = array_sum($ctemp)/3;}
		else 			{ $ctempavg = array_sum($ctemp)/4;}
	}

	if ($ctempavg>99) { $tacol = 'red';}
	else if ($ctempavg>89) { $tacol = 'orange';}
	else if ($ctempavg>85) { $tacol = 'yellow';}
	else if ($ctempavg>79) { $tacol = 'greenlight';}
	else if ($ctempavg>60) { $tacol = 'green';}
	else { $tacol = 'blue';}
	
	for ($j=0; $j<3;$j++) {
		if ($ctemp[$j]==0) {$add[$j] = 'style="padding-left: 12px;"';}
		else		{$add[$j] = '';}
		if ($asic_btemp[$j]==0) {$badd[$j] = 'style="padding-left: 12px;"';}
		else		{$badd[$j] = '';}
	}
	if	($freqavg > 724)	{ $freqcol = 'ffiol';}
	else if ($freqavg > 699)	{ $freqcol = 'fgreen';}
	else if ($freqavg > 634)	{ $freqcol = 'fblue';}
	else if ($freqavg > 549)	{ $freqcol = 'fyellow';}
	else				{ $freqcol = 'fred';}
	
	if ($fan_mode == 'a') { $fanmodecl = 'fiol';}
	else if ($fan_mode == 'off') { $fanmodecl = 'red';}
	else  	 { $fanmodecl = 'badred';}
	if ($fan_check == 'on')	{ $fanckcl = 'green';}
	else		{ $fanckcl = 'badred';}

	$html = "<tr>
		<td><input type=checkbox value=$ip aid=$id></td>
		<td><a href=\"getinfo.php?id=$id&type=$type\" target=\"_blank\">$id</a></td>
		<td>$fw_type</td>
                <td $fwfree>$fw_ver</td>
		<td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td>
		<td>$pool_url[$poolnum]</td>
		<td>$poolnum</td>
		<td>$pool_user[$poolnum]</td>
		<td><span class=\"$upbox\">$uptime</span></td>
		<td class=$freqcol>$freqavg</td>";
	if ($type == 's9') { $html .= "<td>".number_format($hrate_ideal)."</td>"; }
	$html .= "		
		<td><span class=\"box $thcl\">" . number_format($ghs5s). "</span></td>
		<td class=\"ghsav $thavcol\">".number_format($ghsav)."</td>
	";
	if ($type == 's9') {
		$html .= "<td>";
		for ($j=0; $j<3;$j++) { $html .= "<span $add[$j] class=\"box $ccl[$j]\">$ctemp[$j]</span>";}
		$html .= "</td>";
	}
	else {
		$html .= "
		<td><span class=\"box $tacol\">".round($ctempavg) ."&deg;</span></td>
        <td><span class=\"box $bcl[0]\"> $asic_btemp[0]</span><span class=\"box $bcl[1]\"> $asic_btemp[1]</span><span class=\"box $bcl[2]\">$asic_btemp[2]</span><span class=\"box $bcl[3]\">$asic_btemp[3]</span></td>
        <td><span class=\"box $ccl[0]\">$ctemp[0]</span><span class=\"box $ccl[1]\">$ctemp[1]</span><span class=\"box $ccl[2]\">$ctemp[2]</span><span class=\"box $ccl[3]\">$ctemp[3]</span></td>";
	}
	$html .= "<td><span class=\"box $csumcl\">$asic_chip_sum</span></td>
	</tr>";

	return array(2,$ghs5s,$ghsav,$html,$total_power,$ctempavg,$pool_url[$poolnum],$rejrate);
}

$html = '<head>
<link href="main.css" type="text/css" rel="stylesheet"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/mass.js"></script>
<title>MassEdit</title>
</head>
<body><table border=0 cellspacing=0 cellpadding=4><tr class=head>
	<td><input type=checkbox value=true id=all></td>
	<td>ID</td>
	<td>FW</td>
	<td>Ver</td>
	<td>IP</td>
	<td>Pool</td>
	<td>id</td>
	<td>Worker</td>
	<td>Uptime</td>
	<td>Freq</td>
	<td>TH ideal</td>
	<td>TH 5s</td>
	<td>TH Avg</td>
	<td>Chip Temp</td>
	<td>Chips</td>
	</tr>';

$totalavg = 0;
$total_pwr = 0;
$total_temp = 0;
$online = 0;
$offline = 0;
$avail = 0;
$s9numt = $s9num;

foreach($s9 as $x) {

	$vars = miner_details('s9',$x);
	$state = $vars[0];
	$totalhashrate += $vars[1];
	$totalavg += $vars[2];
	$html .= $vars[3];
	$total_pwr += $vars[4];
	$total_temp += $vars[5];
	$poolname = $vars[6];
	if ($vars[5] < 10) {
		#print $vars[4];
		$s9numt -= 1;
	}
	if	($state == 0) {$offline++;}
	else if ($state == 1) {$avail++;}
	else if ($state == 2) {$online++;}
}

if ($avail>0) {$availclass = 'fyellow';} else {$availclass = '';}
if ($offline>0) {$offclass = 'fred';} else { $offclass = '';}

$html .= "</table><br><span class=bold>Total: ". $s9num . " miners (<span class=fgreen>$online</span> / <span class=$availclass>$avail</span> / <span class=$offclass>$offline</span>)" . " | <span class=fblue>". number_format($totalavg/$online/1000,3)."</span> T (S9avg) | " . number_format($totalhashrate/1000,1) . " T | <span class=fblue>".number_format($totalavg/1000,1) ."</span> T(avg) | ".round($total_temp/$s9numt,2)."&deg; (avg) | " .number_format($total_pwr)." W <br>

<div id=pools>
<h2>Add and Switch Pool</h2>
	<input id=urlsw  type=\"text\" name=\"url\" value=\"\" size=30>
	<input id=usersw type=\"text\" name=\"user\" size=10>
	<input id=add  type=\"submit\" value=\"ADD\"> 
	<input id=addnswt  type=\"submit\" value=\"ADD & SWITCH\"> 

<h2>Pool Switch and Delete</h2>
	<input id=poolid type=\"text\" name=\"poolid\" size=3>
	<input id=pooldel type=\"submit\" value=\"DELETE\"> 
 	<input id=switch type=\"submit\" value=\"SWITCH\"> 

<h2>Default Config</h2>
Pool #1: <input id=defpool1 type=\"text\" name=\"url\" value=\"url\" size=30><input id=defusr1 type=\"text\" name=\"user\" value=user size=10><br>
Pool #2: <input id=defpool2 type=\"text\" name=\"url\" value=\"url\" size=30><input id=defusr2 type=\"text\" name=\"user\" value=user size=10><br>
Pool #3: <input id=defpool3 type=\"text\" name=\"url\" value=\"url\" size=30><input id=defusr3 type=\"text\" name=\"user\" value=user size=10><br>
Temp Critical: <input id=deftemp type=text name=temp value=110 size=5><br>
Disable Sensor Scan: <input id=defsensor name=sensor type=checkbox value=\"true\"><br>
Disable Fans Check: <input id=deffanck name=fanck type=checkbox value=\"true\"><br>
<br>	<input id=defsubmit type=\"submit\" value=\"SUBMIT\"> 

<h2>Restart Miner</h2>
	<input id=restart type=\"submit\" value=\"RESTART\">
	
<h2>Delete Miner</h2>
	<input id=delete type=\"submit\" value=\"DELETE\"> 

</div>
";

$exec_time = round(microtime(true) - $start, 3);
$html .=  "<br>Load time: " . $exec_time . " sec (" . date('Y-m-d H:i:s') .")";
$html .=  "</body>";

print $html;
