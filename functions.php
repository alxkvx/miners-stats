<?php

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
    if (!json_decode($buff)) {
        $json = 0;
        print "BAD json, error: " . json_last_error();
    }
    else {
        $json = json_decode($buff,true);
    }
    return $json;
}
