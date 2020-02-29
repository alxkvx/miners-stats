<?php

$type = $_GET['type'];

$html = '<head>
<link href="main.css" type="text/css" rel="stylesheet"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/add.js"></script>
<title>Add</title></head><body>';

if ($type == 'rig') {
    $html .= '
    <div id="singrigdiv">
    <table border=0 cellspacing=0 cellpadding=3>
    <tr>
        <td>ID: <input type=text name=rigid value="" size=3></td>
        <td>Name: <input type=text name=rigname value="" size=3></td>
        <td>IP: <input type=text name=ip value="" size=8></td>
        <td>Port: <input type=text name=port value="" size=5></td>
        <td>GPU Type: <input type=text name=gputype value="" size=5></td>
        <td>Memory Type: <input type=text name=memtype value="" size=1></td>
        <td>GPUs number: <input type=text name=gpusnum value="" size=1></td>
        <td><input id="addrig" type=submit value="ADD"></td>
    </tr>
    </table>
    </div>';
}
else {
    $html .= '
    <div id="singlediv">
    <table border=0 cellspacing=0 cellpadding=3>
    <tr>
        <td>ID: <input type=text name=id value="" size=3></td>
        <td>IP: <input type=text name=ip value="" size=8></td>
        <td>Model: <input type=text name=model value="" size=5></td>
        <td>Fan check: <input type=text name=fanck value="" size=1></td>
        <td>Fan mode: <input type=text name=fanmod value="" size=1></td>
        <td>Disabled: <input type=text name=disabled value="" size=1></td>
        <td>Comment: <input type=text name=comment value="" size=30></td>
        <td>Type: <input type=text name=type value="'.$type.'" size=10></td>
        <td><input id="savesingle" type=submit value="Save"></td>
        <td><input id="scaner" type=submit value="NET Scanner"></td>
    </tr>
    </table>
    </div>
    <div id="scanerdiv">
    <table border=0 cellspacing=0 cellpadding=3>
        <tr>
        <td>IP range:</td>
        <td><input name="oct1" type="text" size="2" value=""></td>
        <td><input name="oct2" type="text" size="2" value=""></td>
        <td><input name="oct3" type="text" size="2" value=""></td>
        <td><input name="oct4" type="text" size="2" value=""></td>
        <td>-</td>
        <td><input name="oct4b" type="text" size="2" value=""></td>
        <td>Skip Existing:<input id="skip" type="checkbox" value="1"></td>
        <td><input id="scanbtn" name="scanbtn" type="button" value="SCAN"></td>
        <td><input id="manual" type=submit value="MANUAL"></td></tr>
    </table>
    </div>
    <div id="scandiv"></div>';	
}
print $html . '</body>';
?>
