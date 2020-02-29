<?php

$start = microtime(true);

$avalon = array(
	        '192.168.11.99',
	);

$avalonnum = count($avalon);

$totalhashrate = 0;

$html = '<link href="main.css" type="text/css" rel="stylesheet"/>';
$html .= "<body><table border=0 cellspacing=0 cellpadding=4><tr class=head><td>ID</td><td>Name</td><td>IP</td><td>Pool</td><td>Worker</td><td>ID</td><td>Diff</td><td>Works</td><td>LS time</td><td>Block</td><td>Uptime</td><td>Fans</td><td>Freq</td><td>TH 5s</td><td>HW</td><td>Reject</td><td>AUC Temp</td><td>Chip Temp</td></tr>";

function get_api($ip,$command) {
		
	$socket = fsockopen($ip, 4028, $err_code, $err_str);
	$data = '{"id":1,"jsonrpc":"2.0","command": "'. $command . '"}' . "\r\n\r\n";
	fputs($socket, $data);
	$buffer = null;
	while (!feof($socket)) { $buffer .= fgets($socket, 4028); }
	if ($socket) {  fclose($socket); }
	$buff = substr($buffer,0,strlen($buffer)-1);
	$buff = preg_replace('/}{/','},{',$buff);
	$buff = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $buff);
	#print $buff . '<br><br>';
	if (!json_decode($buff)) {$html .= "BAD json, error: " . json_last_error();}
	else { $json = json_decode($buff,true);}
	return $json;
}

function miner_details($ip) {
		
	$json = get_api($ip,'summary');
			
	$getworks       = number_format($json['SUMMARY'][0]['Getworks']);
	$elapsed        = $json['SUMMARY'][0]['Elapsed'];
	$blocks         = $json['SUMMARY'][0]['Found Blocks'];
	$accepted       = $json['SUMMARY'][0]['Accepted'];
	$rejected       = $json['SUMMARY'][0]['Rejected'];
	
	$json = get_api($ip,'pools');

	$pool0_status   = $json['POOLS'][0]['Status'];
	$pool1_status   = $json['POOLS'][1]['Status'];
	$pool2_status   = $json['POOLS'][2]['Status'];
	$pool0_prio     = $json['POOLS'][0]['Priority'];
	$pool1_prio     = $json['POOLS'][1]['Priority'];
	$pool2_prio     = $json['POOLS'][2]['Priority'];
	$pool0_url      = $json['POOLS'][0]['URL'];
	$pool1_url      = $json['POOLS'][1]['URL'];
	$pool2_url      = $json['POOLS'][2]['URL'];
	$pool0_user     = $json['POOLS'][0]['User'];
	$pool1_user     = $json['POOLS'][1]['User'];
	$pool2_user     = $json['POOLS'][2]['User'];
	$pool0_diff     = $json['POOLS'][0]['Diff'];
	$pool1_diff     = $json['POOLS'][1]['Diff'];
	$pool2_diff     = $json['POOLS'][2]['Diff'];
	$pool0_works    = $json['POOLS'][0]['Getworks'];
	$pool1_works    = $json['POOLS'][1]['Getworks'];
	$pool2_works    = $json['POOLS'][2]['Getworks'];
	$pool0_lstime   = $json['POOLS'][0]['Last Share Time'];
	$pool1_lstime   = $json['POOLS'][1]['Last Share Time'];
	$pool2_lstime   = $json['POOLS'][2]['Last Share Time'];

	$json = get_api($ip,'stats');
	$html = '';
	$avnum =0;
	$ths =0;

	for ($i=0; $i<2; $i++) {
		$setname        = $json['STATS'][$i]['ID'];
		$auc_temp       = $json['STATS'][$i]['AUC Temperature'];
			
		for ($x=0; $x<5; $x++) {
			$z = $x+1;
			$avid = "MM ID$z";
			$avset[$x]      = $json['STATS'][$i][$avid];
			if (!$avset[$x]) {continue;}
			$arr            = explode(" ", $avset[$x]);
			$name           = preg_replace("/DNA\[(.*)\]/","\$1",$arr[1]);
			$hw             = preg_replace("/HW\[(.*)\]/","\$1",$arr[12]);
			$ctemp0         = preg_replace("/Temp\[(.*)\]/","\$1",$arr[13]);
			$fan            = preg_replace("/Fan\[(.*)\]/","\$1",$arr[15]);
			$ghs5s          = preg_replace("/GHSmm\[(.*)\]/","\$1",$arr[25]);
			$freq           = preg_replace("/Freq\[(.*)\]/","\$1",$arr[27]);

			if ($ctemp0>100) { $ccl0 = 'red';} else if ($ctemp0>90) { $ccl0 = 'orange';} else if ($ctemp0>83) { $ccl0 = 'yellow';} else if ($ctemp0>60) { $ccl0 = 'green';} else { $ccl0 = 'blue';}
			if ($ghs5s>9000) {$thcl = 'greenlight';} else if ($ghs5s>8800) {$thcl = 'green';} else if ($ghs5s>8700) {$thcl = 'blue';} else if ($ghs5s>8600) {$thcl = 'yellow';} else if ($ghs5s>8500) {$thcl = 'orange';} else {$thcl = 'red';}

			if              ($elapsed<180)          {       $uptime = $elapsed . " sec";    }
			else if ($elapsed<3600*2)       {       $uptime = floor($elapsed/60) . " min"; }
			else if ($elapsed<3600*48)      {       $uptime = floor($elapsed/3600) . " H";  }
			else                                            {       $uptime = floor($elapsed/(3600*24)) . " days";  }

			if ($hw > 10000) {      $hw = '<td class="hwred">' . number_format($hw);}
			else                    {       $hw = "<td>" . number_format($hw);      }
			$rejrate = round((100*($rejected/$accepted)), 3);
			$pool0_url = preg_replace("/stratum\+tcp:\/\/(.*)/","\$1",$pool0_url);
			$worker_parts = explode('.', $pool0_user);
			$worker_name = $worker_parts[0];
			$worker_id = $worker_parts[1];
			$fan1cl = 'green';
			if ($fan>5999) { $fan1cl = 'red'; }
			else if ($fan>5000) { $fan1cl = 'yellow'; }
			else if ($fan<3500) { $fan1cl = 'blue'; }
			$ths += $ghs5s;
			$avnum +=1;
			$html .= "<tr>
				<td>$setname</td>
				<td>$name</td>
				<td>$ip</td>
				<td>$pool0_url</td>
				<td>$worker_name</td>
				<td>$worker_id</td>
				<td>$pool0_diff</td>
				<td>$getworks</td>
				<td>$pool0_lstime</td>
				<td>$blocks</td>
				<td>$uptime</td>
				<td><span class=\"box $fan1cl\">$fan</span></td>
				<td>$freq</td>
				<td><span class=\"box $thcl\">".number_format($ghs5s)."</span></td>
				$hw</td>
				<td>$rejrate%</td>
				<td><span class=\"box\">$auc_temp</span></td>
				<td><span class=\"box $ccl0\">$ctemp0</span></td></tr>";
		}
	}
	return array($ths,$html,$avnum);
}


for($x = 0; $x < $avalonnum; $x++) {

	$vars = miner_details($avalon[$x]);
	$totalhashrate += $vars[0];
	$html .= $vars[1];
	$avalons = $vars[2];
}

$totalhashrate = number_format($totalhashrate);
$html .= "</table><br>Total: ". $avalons . " miners" . " / " . $totalhashrate . " Th<br><br>";

$exec_time = round(microtime(true) - $start, 3);
$html .=  "<br><br>Load time: " . $exec_time . " sec (" . date('Y-m-d H:i:s') .")";
$html .=  "</body>";

print $html;

?>



