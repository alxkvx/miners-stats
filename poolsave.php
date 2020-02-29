<?php

$ip = $_GET['ip'];
$pool[0] = $_GET['pool0'];
$pool[1] = $_GET['pool1'];
$pool[2] = $_GET['pool2'];
$user[0] = $_GET['user0'];
$user[1] = $_GET['user1'];
$user[2] = $_GET['user2'];

$connection = ssh2_connect($ip, 22);
ssh2_auth_password($connection, 'root', '1qazXsw2');
ssh2_scp_recv($connection, '/etc/cgminer.conf', 'cgminer_recv.conf');

$conf = json_decode(file_get_contents('cgminer_recv.conf'),true);

for ($x=0;$x<3;$x++) {
    if ($pool[$x]) {
        $conf['pools'][$x]['_id'] = ''.($x+1).'';
        $conf['pools'][$x]['url'] = $pool[$x];
        $conf['pools'][$x]['user'] = $user[$x];
        $conf['pools'][$x]['pass'] = '1';
    }
}

$f = fopen('cgminer.conf', 'w');
fwrite($f, json_encode($conf));
fclose($f);

ssh2_scp_send($connection, 'cgminer.conf', '/etc/cgminer.conf', 0644);

echo "miner $ip default pools set.<br>";

?>

