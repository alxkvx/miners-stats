<?php

$start = microtime(true);

$rigs = json_decode(file_get_contents('json/rig.json'),true);

$html = '<link href="main.css" type="text/css" rel="stylesheet"/><head><title>Rigs</title></head>
<body><table id=rigtbl cellspacing=3 cellpadding=3><tr class=head>
<td>Name</td>
<td>Type</td>
<td>Memory</td>
<td>G#</td>
<td>Miner</td>
<td>Coin</td>
<td>IP</td>
<td>Pool</td>
<td>Uptime</td>
<td>MH/s</td>
<td>T avg</td>
<td>Pwr</td>';
for ($i=0;$i<12;$i++) { $html .= "<td>GPU$i</td>"; }
$html .= '</tr>';

$gpusruntotal = 0;
$gpustotal = 0;
$rigsnum = count($rigs);
$rigspwr = 0;

foreach($rigs as $x) {

        $id     = $x['id'];
        $ip     = $x['ip'];
        $port   = $x['port'];
        $name   = $x['name'];
        $type   = $x['gputype'];
        $gpus   = $x['gpusnum'];
        $gpu_mem = $x['memtype'];
        $gpu_pwr = $x['gpupwr'];
        $disabled = $x['disabled'];

        $gpustotal += $gpus;
        $socket = fsockopen($ip, $port, $err_code, $err_str,0.5);
        if (!$socket) {
                $html .= "<tr>
                <td>$name</td>
                <td>$type</td>
                <td>$gpu_mem</td>
                <td>$gpus</td>
                <td colspan=2><span class=\"box red\">Offline</span></td>
                <td><span class=\"red\">$ip</span></td>
                </tr>";
                continue;
        }
	$data = '{"id":1,"jsonrpc":"2.0","method":"miner_getstat1"}' . "\r\n\r\n";
        fputs($socket, $data);
        $buffer = null;
        while (!feof($socket)) { $buffer .= fgets($socket); }
        if ($socket) {  fclose($socket); }
        #print $buffer;
        $json = json_decode($buffer,true);

        $miner          = $json['result'][0];
        $elapsed        = $json['result'][1];
        $total_eth      = $json['result'][2];
        $eth_mh         = $json['result'][3];
        $total_xvg      = $json['result'][4];
        $xvg_mh         = $json['result'][5];
        $temp_fan       = $json['result'][6];
        $pools          = $json['result'][7];
        $eth_pool_inv_sw= $json['result'][8];
        $eth_acc_shrs   = $json['result'][9];
        $eth_rej_shrs   = $json['result'][10];
        $eth_inv_shrs   = $json['result'][11];
        $xvg_acc_shrs   = $json['result'][12];
        $xvg_rej_shrs   = $json['result'][13];
        $xvg_inv_shrs   = $json['result'][14];

        $miner_arr = explode(" - ", $miner);
        $miner_name = $miner_arr[0];
        $coin = $miner_arr[1];

        if ($elapsed<60*2) {
                $uptime = $elapsed . " min";
        }
        else if ($elapsed<60*48) {
                $uptime = floor($elapsed/60) . " H";
        }
        else {
                $uptime = floor($elapsed/(60*24)) . " days";
        }

        $eth_pool = preg_replace("/(.*);.*/","\$1",$pools);
        $eth_all = preg_replace("/(\d+);.*/","\$1",$total_eth);
        $xvg_all = preg_replace("/(\d+);.*/","\$1",$total_xvg);
        $eth_all = number_format($eth_all/1000, 1);
        $xvg_all = number_format($xvg_all/1000000, 2);
        if ($type == 'RX570')   {$tcol = 'amd';}
	
	$tavg = 0;
        $gpu_eth = [];
        $gpu_temp =[];
        $gpu_fan = [];
        $gpus_all = explode(";", $eth_mh);
        $temp_fanall = explode(";", $temp_fan);
        $gpus_num = count($gpus_all);
        $j=0;                                                                                                                                                            for ($i=0;$i<$gpus_num;$i++){                                                                                                                                            $gpu_eth[$i]    = number_format($gpus_all[$i]/1000, 2);
                $gpu_temp[$i]   = $temp_fanall[$j];
                $gpu_fan[$i]    = $temp_fanall[$j+1];
                $tavg+= $gpu_temp[$i];
                $j+=2;
        }
        $rigpwr = $gpus_num*($gpu_pwr + 22) + 100;
        $rigspwr += $rigpwr;
        $gpusruntotal += $gpus_num;
        $tavg = round($tavg/$gpus, 1);
        $ethtotal += $eth_all;
        $xvgtotal += $xvg_all;
        if (preg_match("/ethermine.org/", $eth_pool)) { $eth_pool = 'Ethermine';}
        else if (preg_match("/etcget.net/", $eth_pool)) { $eth_pool = 'Max';}
        $html.= "<tr>
        <td>$name</td>
        <td><span class=\"box $tcol\">$type</span></td>
        <td>$gpu_mem</td>
        <td>$gpus</td>
        <td>$miner_name</td>
        <td>$coin</td>
        <td>$ip</td>
        <td>$eth_pool</td>
        <td>$uptime</td>
        <td class=fblue>$eth_all</td>
        <td>${tavg}&deg;</td>
        <td>${rigpwr}W</td>";
	
	for ($i=0;$i<$gpus;$i++){ 
		if ($gpu_eth[$i] === NULL) {$html.= "<td class=gpus><span class=\"box red\">X</span>";}
		else {
			if ($gpu_eth[$i] > 31) {$ecol = 'fontgreen';} else if ($gpu_eth[$i] > 29.9) {$ecol = 'fontblue';} else if ($gpu_eth[$i] > 27) {$ecol = 'fontyell';} else {$ecol = 'fred';}
			if ($gpu_temp[$i]>78) { $col = 'red';} else if ($gpu_temp[$i]>71) { $col = 'orange';} else if ($gpu_temp[$i]>67) { $col = 'yellow';} else if ($gpu_temp[$i]>59) { $col = 'green';} else if ($gpu_temp[$i]>50) { $col = 'blue';} else { $col = 'fiol';}
			if ($gpu_fan[$i]>90) { $fcol = 'red';} else if ($gpu_fan[$i]>75) { $fcol = 'orange';} else if ($gpu_fan[$i]>50) { $fcol = 'yellow';} else if ($gpu_fan[$i]>30) { $fcol = 'blue';} else if ($gpu_fan[$i]==0) { $fcol = 'badred';} else { $fcol = 'fiol';}
			$html .= "<td class=gpus><span class=\"box $ecol\">$gpu_eth[$i]</span><br><span class=\"box $col\">$gpu_temp[$i]&deg;</span><span class=\"box $fcol\">$gpu_fan[$i]%</span></td>";
		}
	}
	$html.= "</tr>";

}

$gpusoff = $gpustotal - $gpusruntotal;
$html.= "<tr><td colspan=2><span class=\"box fiol\"><a href=\"add.php?type=rig\" target=\"_blank\">ADD</a></span></td></tr></table>";

$html.= "<br>Total: ". $rigsnum . " Rigs | ". $gpusruntotal . "/<span style=color:#ff6262>" . $gpusoff . "</span> GPUs | <span class=fblue>". $ethtotal . "</span> MH/s | " . number_format($rigspwr) . " W<br>";

$exec_time = round(microtime(true) - $start, 3);
$html .=  "<br>Load time: " . $exec_time . " sec (" . date('Y-m-d H:i:s') .")";

echo $html;
