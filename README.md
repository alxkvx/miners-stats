# Miners Stats
This is simple php page that collects stats from miners and shows it on single page.

![alt text](https://raw.githubusercontent.com/alxkvx/miners-stats/master/miners.JPG)
![alt text](https://raw.githubusercontent.com/alxkvx/miners-stats/master/miner_info.JPG)
![alt text](https://raw.githubusercontent.com/alxkvx/miners-stats/master/rigs.JPG)

## Features:
- Shows all miners stats on sinle page
- Miner restart
- Miner detailed info
- Change/Add/Remove pools on fly
- Change miner config: Freq/Volt/Pools (Braiins OS only)
- Mass actions: (change pools/config, restart, delete)

## Supported Miners:
- Antminer S9
- Antminer L3+
- Avalon 841
- Rigs with claymore/phoeninx miners

## Requirements:
- Linux or windows pc/laptop/raspberryPi/server/VM on the same network with miners
- running web server: apache/nginx etc.
- php with json module

## Installation:
download files into web server doc root (f.e. /var/www/html):
```
root@raspberrypi:/var/www/html/# git clone https://github.com/alxkvx/miners-stats.git
```
change owner of files to Apache user:
```
root@raspberrypi:/var/www/html# chown -R www-data.www-data miners-stats/
```
Add your miners using **ADD** button on the page.

## Access:
http://<machine_IP>/miners-stats/s9.php  
http://<machine_IP>/miners-stats/l3.php  
http://<machine_IP>/miners-stats/rigs.php  
http://<machine_IP>/miners-stats/avalons.php  
http://<machine_IP>/miners-stats/massedit.php

## Enjoy!
