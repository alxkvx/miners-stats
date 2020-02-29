<?php
error_reporting(E_ERROR | E_PARSE);
$start = microtime(true);

function api($ip,$command) {

        $socket = fsockopen($ip, 4028, $err_code, $err_str, 0.2);
        if (!$socket) {
                $socket2 = fsockopen($ip, 80, $err_code, $err_str, 0.2);
                        if ($socket2)   {return 1;}
                        else            {return 0;}
        }
        $data = '{"id":1,"jsonrpc":"2.0","command": "'. $command . '"}' . "\r\n\r\n";
        fputs($socket, $data);
        $buffer = null;
        while (!feof($socket)) { $buffer .= fgets($socket, 4028); }
        if ($socket) {  fclose($socket); }
        $buff = substr($buffer,0,strlen($buffer)-1);
        $buff = preg_replace('/}{/','},{',$buff);
        $buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
        if (!json_decode($buff)) { print "BAD json, error: " . json_last_error();}
        else { $json = json_decode($buff,true);}
        return $json;
}

function miner_details($type,$id,$arr) {

	$ip		= $arr[$id]['ip'];
    $com	= $arr[$id]['comment'];
	$disable= $arr[$id]['disabled'];
	$fanmod = $arr[$id]['fanmod'];
	$fanck  = $arr[$id]['fanck'];
	$model	= $arr[$id]['model'];

	$headtbl = "<div id='divedit'>
	<div id='editclose'>X</div>
	<div id='editfld'>
		EDIT MINER INFO<br><br>
		ID: <input type=text name=id value=$id size=3> 
		IP: <input type=text name=ip value=$ip size=8> 
		Model: <input type=text name=model value=\"$model\" size=6> 
		Fan check: <input type=text name=fanck value=\"$fanck\" size=1> 
		Fan mode: <input type=text name=fanmod value=\"$fanmod\" size=1> 
		Disabled: <input type=text name=disabled value=\"$disable\" size=1> 
		Comment: <input type=text name=comment value=\"$com\" size=30>
		Type: <input type=text name=type value=$type size=2>
		<input id=editsave type=submit value=SAVE>
	</div>
	</div>
	<div id='divconf'>
		<div id='confclose'>X</div>
		<div id='conffld'>
		BRAIN OS Config:
		<table cellpadding=3>
			<tr><td align=right>Pool #0:</td><td><input type=text name=pool0 value=\"\" size=35></td><td>User: <input type=text name=user0 value=\"\" size=10></td></tr>
			<tr><td align=right>Pool #1:</td><td><input type=text name=pool1 value=\"\" size=35></td><td>User: <input type=text name=user1 value=\"\" size=10></td></tr>
			<tr><td align=right>Pool #2:</td><td><input type=text name=pool2 value=\"\" size=35></td><td>User: <input type=text name=user2 value=\"\" size=10></td></tr>
			<tr><td align=right>Freq #0:</td><td><input type=text name=freq0 value=\"\" size=5> Volt: <input type=text name=volt0 value=\"\" size=5></td></tr>
			<tr><td align=right>Freq #1:</td><td><input type=text name=freq1 value=\"\" size=5> Volt: <input type=text name=volt1 value=\"\" size=5></td></tr>
			<tr><td align=right>Freq #2:</td><td><input type=text name=freq2 value=\"\" size=5> Volt: <input type=text name=volt2 value=\"\" size=5></td></tr>
			<tr><td align=right>Temp Critical:</td><td><input type=text name=temp value=110 size=5></td></tr>
			<tr><td align=right>Disable Sensor Scan:</td><td><input name=sensor type=checkbox value=true></td></tr>
			<tr><td align=right>Disable Fans Check:</td><td><input name=fanck type=checkbox value=true></td></tr>
		</table>
			<input id=confsave type=submit value=Save>
		</div>
	</div>
	<table border=0 cellspacing=0 cellpadding=4>
<tr class=head><td>ID</td><td>Model</td><td>Type</td><td>Miner</td><td>IP</td><td>Pool</td><td>Worker</td><td>Diff</td><td>Works</td><td>LS time</td><td>Block</td><td>Uptime</td><td>Fans</td><td>Volt</td><td>Freq</td><td>TH ideal</td><td>TH 5s</td><td>TH Avg</td><td>HW</td><td>Reject</td><td>T(avg)</td><td>Board Temp</td><td>Chip Temp</td><td>Chips</td><td>Watt</td><td>Action</td><td>Comment</td></tr>";

	if ($disable == 1) {
		return $headtbl . "<tr>
		<td class=wid>$id</td>
		<td>$model</td>
		<td></td>
		<td><span class=\"box badred\">Disabled</span></td>		
		<td><span class=\"box red\"><a href=\"http://$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=21 align=right><span id='edit'>Edit</span></td>
		<td>$com</td></tr></table>";
	}

	$json = api($ip,'summary+pools+stats');
	if ($json == 0) {
		return $headtbl ."<tr>
		<td class=wid>$id</td>
		<td>$model</td>
		<td></td>
		<td><span class=\"box red\">Offline</span></td>
		<td><span class=\"box red\"><a href=\"http://$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=21 align=right><span id='edit'>Edit</span></td>
		<td>$com</td></tr></table>";
	}
	else if ($json == 1) {
		return $headtbl . "<tr>
		<td class=wid>$id</td>
		<td>$model</td>
		<td></td>
		<td><span class=\"box yellow\">Web</span></td>
		<td><span class=\"box\"><a href=\"http://$ip\" target=\"_blank\">$ip</a></span></td>
		<td colspan=21></td>
		<td><span id='grestart'>Restart</span> ( <span id='restartminer'>Miner</span> ) | <span id='edit'>Edit</span> | <span id='config'>Config</span></td>
		<td>$com</td></tr></table>";
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
		$pool_accept[$i]   = $json['pools'][0]['POOLS'][$i]['Accepted'];
		$pool_reject[$i]   = $json['pools'][0]['POOLS'][$i]['Rejected'];
		$pool_discard[$i]  = $json['pools'][0]['POOLS'][$i]['Discarded'];
		$pool_aboost[$i]   = $json['pools'][0]['POOLS'][$i]['Asic Boost'];
		$pool_diffa[$i]    = $json['pools'][0]['POOLS'][$i]['Difficulty Accepted'];
		$pool_diffr[$i]    = $json['pools'][0]['POOLS'][$i]['Difficulty Rejected'];
	}

 	$miner_type     = $json['stats'][0]['STATS'][0]['Type'];
 	$miner_ver      = $json['stats'][0]['STATS'][0]['Miner'];
 	$miner_compile  = $json['stats'][0]['STATS'][0]['CompileTime'];
 	$freq           = $json['stats'][0]['STATS'][1]['frequency'];

	if ($type == 's9') {
        $bmminer_ver    = $json['stats'][0]['STATS'][0]['BMMiner'];
		for ($x=0;$x<3;$x++) {
			if ($miner_type == 'Antminer S9k') {
				$y = 1 + $x;
                		$ctemp[$x]       = $json['stats'][0]['STATS'][1]["temp$y"];
                		$asic_btemp[$x]  = $json['stats'][0]['STATS'][1]["temp2_$y"];
                		$asic_freq[$x]   = $json['stats'][0]['STATS'][1]["freq$y"];
			}
			else {
				$y = 6 + $x;
                		$ctemp[$x]       = $json['stats'][0]['STATS'][1]["temp2_$y"];
                		$asic_btemp[$x]  = $json['stats'][0]['STATS'][1]["temp$y"];
                		$asic_freq[$x]   = $json['stats'][0]['STATS'][1]["freq_avg$y"];
			}
                	$asic_chips[$x]  = $json['stats'][0]['STATS'][1]["chain_acn$y"];
                	$asic_hrideal[$x]= $json['stats'][0]['STATS'][1]["chain_rateideal$y"];
                	$asic_hr[$x]     = $json['stats'][0]['STATS'][1]["chain_rate$y"];
                	$asic_hw[$x]     = $json['stats'][0]['STATS'][1]["chain_hw$y"];
                	$asic_chain[$x]  = $json['stats'][0]['STATS'][1]["chain_acs$y"];
			$asic_power[$x]	 = $json['stats'][0]['STATS'][1]["chain_consumption$y"];
			if (!$asic_power[$x]) {$asic_power[$x]	 = $json['stats'][0]['STATS'][1]["chain_power$y"];}
			$asic_volt[$x]	 = $json['stats'][0]['STATS'][1]["voltage$y"];
			if (!$asic_volt[$x]) {
				$asic_volt[$x] = $json['stats'][0]['STATS'][1]["chain_vol$y"]/100;
			}
		}
		if ($miner_type == 'Antminer S9k') {
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
		$fan_mode	= $json['stats'][0]['STATS'][1]['manual_fan_mode'];
                $hrate_ideal    = $json['stats'][0]['STATS'][1]['total_rateideal'];
		$temp_num	= $json['stats'][0]['STATS'][1]['temp_num'];
		
		$freqavg = round(array_sum($asic_freq)/3);
                $asic_chip_sum = array_sum($asic_chips);
                $miner_type     =  preg_replace('/S9S9/','S9',$miner_type);
		for ($i=0; $i<3; $i++) {
			if ($asic_btemp[$i]>89) { $bcl[$i] = 'red';}
			else if ($asic_btemp[$i]>80) { $bcl[$i] = 'orange';}
			else if ($asic_btemp[$i]>75) { $bcl[$i] = 'yellow';}
			else if ($asic_btemp[$i]>50) { $bcl[$i] = 'green';}
			else if ($asic_btemp[$i]>15) { $bcl[$i] = 'blue';}
			else { $bcl[$i] = 'badred';}
			if ($ctemp[$i]>99) { $ccl[$i] = 'red';}
			else if ($ctemp[$i]>90) { $ccl[$i] = 'orange';}
			else if ($ctemp[$i]>85) { $ccl[$i] = 'yellow';}
			else if ($ctemp[$i]>79) { $ccl[$i] = 'greenlight';}
			else if ($ctemp[$i]>60) { $ccl[$i] = 'green';}
			else if ($ctemp[$i]>15) { $ccl[$i] = 'blue';}
			else {$ccl[$i] = 'badred';}
		}
                if ($ghs5s>15000) {$thcl = 'fiol';}
		else if ($ghs5s>14500) {$thcl = 'greenlight';}
		else if ($ghs5s>14000) {$thcl = 'green';}
		else if ($ghs5s>13450) {$thcl = 'blue';}
		else if ($ghs5s>13000) {$thcl = 'yellow';}
		else if ($ghs5s>10000) {$thcl = 'orange';} else {$thcl = 'red';}
                if ($asic_chip_sum<189 && $miner_type != 'Antminer S9k') {$csumcl = 'red';} 
                else if ($asic_chip_sum<180 && $miner_type == 'Antminer S9k') {$csumcl = 'red';} 
		else {$csumcl = '';}
        }
        else {
			for ($x=0;$x<4;$x++) { $y = 1 + $x;
				$asic_btemp[$x] = $json['stats'][0]['STATS'][1]["temp$y"];
				$ctemp[$x]      = $json['stats'][0]['STATS'][1]["temp2_$y"];
				$asic_chips[$x] = $json['stats'][0]['STATS'][1]["chain_acn$y"];
				$asic_freq[$x]  = $json['stats'][0]['STATS'][1]["frequency$y"];
				$asic_chain[$x] = $json['stats'][0]['STATS'][1]["chain_acs$y"];
				$asic_power[$x] = $json['stats'][0]['STATS'][1]["watt$y"];
                $asic_volt[$x]  = $json['stats'][0]['STATS'][1]["volt$y"];
				$asic_hr[$x]    = $json['stats'][0]['STATS'][1]["chain_rate$y"];
				$asic_hw[$x]    = $json['stats'][0]['STATS'][1]["chain_hw$y"];
                        if ($asic_btemp[$x]>80) { $bcl[$x] = 'red';}
			else if ($asic_btemp[$x]>70) { $bcl[$x] = 'orange';}
			else if ($asic_btemp[$x]>65) { $bcl[$x] = 'yellow';}
			else if ($asic_btemp[$x]>50) { $bcl[$x] = 'green';}
			else 			     { $bcl[$x] = 'blue';}
			if ($ctemp[$x]>80) { $ccl[$x] = 'red';}
			else if ($ctemp[$x]>70) { $ccl[$x] = 'orange';}
			else if ($ctemp[$x]>65) { $ccl[$x] = 'yellow';}
			else if ($ctemp[$x]>50) { $ccl[$x] = 'green';}
			else 			{ $ccl[$x] = 'blue';}
		}
                $fan1           = $json['stats'][0]['STATS'][1]['fan1'];
                $fan2           = $json['stats'][0]['STATS'][1]['fan2'];
		$asic_chip_sum = array_sum($asic_chips);
		$freqavg = round(array_sum($asic_freq)/4);
		if ($freqavg<1) {$freqavg = $freq;}
                if ($ghs5s>650) {$thcl = 'greenlight';}
		else if ($ghs5s>600) {$thcl = 'green';}
		else if ($ghs5s>500) {$thcl = 'blue';}
		else if ($ghs5s>450) {$thcl = 'yellow';}
		else if ($ghs5s>400) {$thcl = 'orange';}
		else {$thcl = 'red';}
		if ($asic_chip_sum<288) {$csumcl = 'red';} else {$csumcl = '';}
	}

	$total_power	= array_sum($asic_power);
	if      ($elapsed<180)          {       $uptime = $elapsed . " sec";$upbox ='box red';}
	else if ($elapsed<3600*2)       {       $uptime = floor($elapsed/60) . " min";$upbox ='box yellow'; }
	else if ($elapsed<3600*48)      {       $uptime = floor($elapsed/3600) . " H";  $upbox =' box blue';}
	else                            {       $uptime = floor($elapsed/(3600*24)) . " days";  }

	if (preg_match('/braiins/', $miner_type)) { $miner_type = 'Braiins OS';}
	else if (preg_match('/vnish (.*)\)/', $miner_type, $vers)) {$miner_type = "ASICDIP $vers[1]";}
        if ($hw > 100000) {     $hw = '<td class="hwred">' . number_format($hw);}
        else                    {       $hw = "<td>" . number_format($hw);      }
        if ($fan1 == 0) { $fan1 = $fan3; }
        $rejrate = round((100*($rejected/$accepted)), 3);
	for ($x=0;$x<6;$x++) {
        	if ($pool_prio[$x] == 0 ) {$poolnum = $x;break;}
	}

	$pool_url_main = preg_replace("/stratum\+tcp:\/\/(.*):\d+/","\$1",$pool_url[$poolnum]);
	if (preg_match('/bOS/', $miner_ver)) { $miner_ver = preg_replace("/bOS_am1-s9-(.*?)-.*/","\$1",$miner_ver); }
	$worker_parts = explode('.', $pool_user[$poolnum]);
	$worker_name = $worker_parts[0];
	$worker_id = $worker_parts[1];
	$fan1cl = $fan2cl = 'green';
	if ($fan1>5999) { $fan1cl = 'red'; }
	else if ($fan1>5000) { $fan1cl = 'yellow'; }
	else if ($fan1<3500) { $fan1cl = 'blue'; }
	if ($fan2>5999) { $fan2cl = 'red'; }
	else if ($fan2>5000) { $fan2cl = 'yellow'; }
	else if ($fan2<3500) { $fan2cl = 'blue'; }
    $difftotal = round($ghs5s - $hrate_ideal);
	for ($i=0;$i<4;$i++)	{ 
		$diff[$i] = round($asic_hr[$i] - $asic_hrideal[$i]);
        	if ($diff[$i]>0) {$dfcol[$i]='green';}
		else if ($diff[$i] < -700) {$dfcol[$i]='red';}
		else if ($diff[$i] < -200) {$dfcol[$i]='orange';}
		else if ($diff[$i] < -50) {$dfcol[$i]='yellow';}
		else {$dfcol[$i]='blue';}
	}
	if ($difftotal>0) {$dftotcol='green';}
	else if ($difftotal < -700) {$dftotcol='red';}
	else if ($difftotal < -250) {$dftotcol='orange';}
	else if ($difftotal < -50) {$dftotcol='yellow';}
	else {$dftotcol='blue';}
	$thdiff = $hrate_ideal - $ghsav;
	if ($thdiff<0)  {$thavcol = 'fgreen';}
	else if ($thdiff>999)   {$thavcol = 'fred';}
	else if ($thdiff>400)   {$thavcol = 'forange';}
	else if ($thdiff>150)   {$thavcol = 'fyellow';}
	else    {$thavcol = 'fblue';}
	if ($total_power == 0) {
		if ($ghsav>16000) 	{ $total_power = 1550;}
		else if ($ghsav>15500)	{ $total_power = 1500;}
		else if ($ghsav>15000)	{ $total_power = 1450;}
		else if ($ghsav>14500)	{ $total_power = 1400;}
		else if ($ghsav>14000)	{ $total_power = 1300;}
		else if ($ghsav>13500)	{ $total_power = 1260;}
		else if ($ghsav>13000)	{ $total_power = 1250;}
		else if ($ghsav>12500)	{ $total_power = 1200;}
		else if ($ghsav>12000)	{ $total_power = 1170;}
		else if ($ghsav>11500)	{ $total_power = 1150;}
		else if ($ghsav>11000)	{ $total_power = 1100;}
		else 			{ $total_power = 1000;}
	}
	if ($ctemp[0] == 0) {            $ctempavg = ($ctemp[1] + $ctemp[2])/2;      }
	else {
		if ($type == 's9') {$ctempavg = array_sum($ctemp)/3;}       
		else 		{$ctempavg = array_sum($ctemp)/4;}
	}
	if ($ctempavg>99) { $tacol = 'red';}
	else if ($ctempavg>90) { $tacol = 'orange';}
	else if ($ctempavg>85) { $tacol = 'yellow';}
	else if ($ctempavg>79) { $tacol = 'greenlight';}
	else if ($ctempavg>60) { $tacol = 'green';}
	else { $tacol = 'blue';}
	if ($fanmod == 'a') { $fanmodecl = 'fiol';}
	else if ($fanmod == 'm') { $fanmodecl = 'red';}
	else	{ $fanmodecl = 'badred';}
			
	$html = $headtbl ."<tr>
 		<td class=wid>$id</td>
 		<td>$model</td>
 		<td class=type>$miner_type</td>
 		<td class=miner>$miner_ver</td>
 		<td><a href=\"http://$ip\" target=\"_blank\">$ip</a></td>
 		<td>$pool_url_main</td>
 		<td class=wname>$pool_user[$poolnum]</td>
 		<td class=diff>$pool_diff[$poolnum]</td>
 		<td class=works>$getworks</td>
 		<td class=lstime>$pool_lstime[$poolnum]</td>
 		<td class=blocks>$blocks</td>
 		<td><span class=\"uptime $upbox\">$uptime</span></td>
 		<td><span class=\"box $fanmodecl\">$fanmod</span><span class=\"fan1 box $fan1cl\">$fan1</span><span class=\"fan2 box $fan2cl\">$fan2</span></td>
 		<td class=volt>$voltavg</td>
 		<td class=freq>$freqavg</td>
 		<td class=hrideal>". number_format(round($hrate_ideal))."</td>
 		<td><span class=\"hrate box $thcl\">" . number_format($ghs5s). "</span></td>                
		<td class=\"ghsav $thavcol\">".number_format($ghsav)."</td>
 		$hw</td>
 		<td class=rrate>$rejrate%</td>
		<td><span class=\"box $tacol\">".round($ctempavg) ."&deg;</span></td>
		<td><span class=\"box $bcl[0]\"> $asic_btemp[0]</span><span class=\"box $bcl[1]\"> $asic_btemp[1]</span><span class=\"box $bcl[2]\">$asic_btemp[2]</span>";
		if ($type == 'l3') { $html .= "<span class=\"box $bcl[3]\">$asic_btemp[3]</span>";} 
		$html .= "</td><td><span class=\"box $ccl[0]\">$ctemp[0]</span><span class=\"box $ccl[1]\">$ctemp[1]</span><span class=\"box $ccl[2]\">$ctemp[2]</span>";
		if ($type == 'l3') { $html .= "<span class=\"box $ccl[3]\">$ctemp[3]</span>";}
		$html .= "</td><td class=reload rel=\"$ip\"><span class=\"chips box $csumcl\">$asic_chip_sum</span></td>
		<td>". number_format($total_power) ."</td>
		<td><span id='grestart'>Restart</span> ( <span id='restartminer'>Miner</span> ) | <span id='edit'>Edit</span> | <span id='config'>Config</span></td>
		<td>$com</td>
		</tr></table><br>";

	$html .= "<div class=pools><table border=0 cellspacing=0 cellpadding=7><tr class=head><td>#</td><td>Pool URL</td><td>Worker</td><td>AB</td><td>Status</td><td>Priority</td><td>Diff</td><td>Works</td><td>Accepted</td><td>Rejected</td><td>Discarded</td><td>DiffA</td><td>DiffA(%)</td><td>DiffR</td><td>LSTime</td><td>Action</td><tr>";

        $pworks_total = 0;
        $pdiffa_total = 0;
        $pdiffr_total = 0;
        for ($i=0; $i<6; $i++) {
			$pworks_total += $pool_works[$i];
			$pdiffa_total += $pool_diffa[$i];
			$pdiffr_total += $pool_diffr[$i];
        } 
        for ($i=0; $i<6; $i++) {
			$hlight ='';
			$pdiffa_prc = round(100*$pool_diffa[$i]/$pdiffa_total,2);
			if ($pool_status[$i] == 'Alive') {$poolcol[$i] = 'fgreen';}
			else {$poolcol[$i] = 'fred';}
			if ($pool_status[$i] == 'Alive' && $pool_prio[$i] == 0) { $hlight= 'class=rowhiglt';}
			if ($pool_aboost[$i] == true) {$abcol[$i] = 'fgreen'; $abval[$i] = 'A';}
			else if ($pool_aboost[$i] == false && $pool_url[$i]) {$abcol[$i] = 'fred'; $abval[$i] = 'N';}
			else { $abval[$i] = '';}
			$html .= "<tr $hlight>
				<td>$i</td>
				<td>$pool_url[$i]</td>
				<td>$pool_user[$i]</td>
				<td class=$abcol[$i]>$abval[$i]</td>
				<td class=$poolcol[$i]>$pool_status[$i]</td>
				<td>$pool_prio[$i]</td>
				<td>$pool_diff[$i]</td>
				<td>$pool_works[$i]</td>
				<td>$pool_accept[$i]</td>
				<td>$pool_reject[$i]</td>
				<td>$pool_discard[$i]</td>
				<td>".number_format($pool_diffa[$i])."</td>
				<td>$pdiffa_prc%</td>
				<td>$pool_diffr[$i]</td>
				<td>$pool_lstime[$i]</td>
				<td><span class='pooldisable' opt=$i>Disable</span> | <span class='poolenable' opt=$i>Enable</span> | <span class='pooldel' opt=$i>Del</span> | <span class='poolprio' opt=$i>Prio</span></td><tr>";
        }
        $html .= "<tr><td></td><td>Total</td><td></td><td></td><td></td><td></td><td>$pworks_total</td><td></td><td></td><td></td><td>".number_format($pdiffa_total)."</td><td>100%</td><td>".number_format($pdiffr_total)."</td><td></td><td></td><td><input type=\"text\" name=\"url\" value=\"\" size=37><input type=\"text\" name=\"user\" value=\"".$pool_user[$poolnum]."\" size=12><input id=pooladd type=\"submit\" value=\"Add\"><input id=pooladdnsw type=\"submit\" value=\"Switch\"></td><tr></table></div><br>";
	
        $html .= "<div class=asics><table border=0 cellspacing=0 cellpadding=6>
        <tr class=head><td>Chain</td><td>Chips</td><td>Freq</td><td>Volt</td><td>Watt</td><td>TH Ideal</td><td>TH Real</td><td>TH Diff</td><td>HW</td><td>Chips Chain</td><tr>";
	if ($type == 's9') {$z = 3;}
	else		{$z = 4;}
	for ($i=0;$i<$z;$i++) {
	
		if ($asic_chips[$i]<63 && $miner_type != 'Antminer S9k') {$chpcol[$i] = 'red';}
		else if ($asic_chips[$i]<60 && $miner_type == 'Antminer S9k') {$chpcol[$i] = 'red';}
		else {$chpcol[$i] = '';} 
		$html .= "<tr><td>#".($i+6)."</td><td><span class=\"box $chpcol[$i]\">$asic_chips[$i]</span></td><td>$asic_freq[$i]</td><td>$asic_volt[$i]</td><td>$asic_power[$i]</td><td>".number_format($asic_hrideal[$i])."</td><td>".number_format($asic_hr[$i])."</td><td><span class=\"box $dfcol[$i]\">$diff[$i]</span></td><td>$asic_hw[$i]</td><td>$asic_chain[$i]</td><tr>";
        }
	$html .= "<tr class=bold><td>Total</td><td></td><td></td><td></td><td>".number_format($total_power)."</td><td>".number_format($hrate_ideal)."</td><td>".number_format($ghs5s)."</td><td><span class=\"box $dftotcol\">".number_format($difftotal)."</span></td><td></td><td></td><tr>
        </table></div>";

	return $html;
}

$htm = '<head><link href="main.css" type="text/css" rel="stylesheet"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/getinfo.js"></script>';

$type = $_GET['type'];
if (!$type) {$type = 's9';}

if ($_GET['id']) {
	$id = $_GET['id'];
	if ($type == 's9') {
		$htm .= '<title>S9: '.$id.'</title></head><body>';
		$jsonfile = file_get_contents('json/s9.json');
		$arr = json_decode($jsonfile,true);
	}
	else if ($type == 'l3') {
		$htm .= '<title>L3: '.$id.'</title></head><body>';
		$jsonfile = file_get_contents('json/l3.json');
		$arr = json_decode($jsonfile,true);

	}
	$ip  = $arr[$id]['ip'];
	$com = $arr[$id]['comment'];
	$disable = $arr[$id]['disabled'];
	$fanmode = $arr[$id]['fanmod'];
	if ($ip) {
		$htm .= miner_details($type,$id,$arr);
	}
	else {
		$htm .= "Bad ID: $id<br>";
	}
}
else if ($_GET['ip']) 	{ 
	$ip = $_GET['ip'];
	$htm .= '<head><title>ASIC: '.$ip.'</title></head><body>';
	$htm .= miner_details($type,0,$ip);
}
else { 	$htm .= "Bad ID or IP<br>";}

$exec_time = round(microtime(true) - $start, 3);

print "$htm<br>Load time: " . $exec_time . " sec (" . date('Y-m-d H:i:s') .")<span><br></span></body>";

?>
