<?php
//ini_set("display_errors", 0);

$spoj=mysql_connect("host","user","pass"); //server

if (!$spoj)
    die('ERROR: Nemohu se pøipojit do databáze: ' . mysql_error());

mysql_select_db("db",$spoj); //server
mysql_query("SET CHARSET cp1250");

$current_npc = 40022;
$ilvl = array(232, 245, 245, 258);
$subname = array(0 => 'Normal 10', 1 => 'Normal 25', 2 => 'Heroic 10', 3 => 'Heroic 25');
for($diff = 0; $diff < 4; $diff++)
{
    $heroic = ($diff == 2 || $diff == 3) ? '' : 'not ';
    $il = $ilvl[$diff];
    $sql = "SELECT * FROM `item_template` WHERE `ItemLevel`=".$il." and ".$heroic."flags & 8 and `name` NOT LIKE 'Relentless %' and `name` NOT LIKE 'Furious %' and name not like 'Battlemaster%' and quality = 4 and entry > 46097";
    $vys=mysql_query($sql,$spoj);
    $count = 0;

    addVendor($subname[$diff]);

    $curr_line = 0;
    if(!$vys)
        echo 'ERROR: Chyba spojení : '.mysql_error().'<br>';
    while ($tab=mysql_fetch_array($vys))
    {
        $curr_line++;
        if($curr_line >150)
        {
            addVendor($subname[$diff]);
            $curr_line = 0;
        }
        $pastesql = "INSERT IGNORE INTO npc_vendor VALUES ('".$current_npc."', '".$tab['entry']."', '0', '0', '0');";
        echo $pastesql.'<br>';
    }
    
}
function addVendor($sub)
{
    global $current_npc;
    global $count;
    $current_npc++;
    $count++;
    $deletesql = "DELETE FROM creature_template WHERE entry = ".$current_npc.";";
    $deletevendorsql = "DELETE FROM npc_vendor WHERE entry = ".$current_npc.";";
    $vendorsql = "INSERT IGNORE INTO `creature_template` VALUES (".$current_npc.", 0, 0, 0, 0, 0, 31953, 0, 0, 0, 'Spammca', 'ToC - ".$sub.' - vendor '.$count."', '', 0, 75, 75, 21270, 21270, 0, 0, 8219, 35, 35, 4224, 1, 1.14286, 1.5, 0, 342, 485, 0, 392, 1, 2000, 0, 1, 32768, 0, 0, 0, 0, 0, 0, 295, 438, 68, 7, 4096, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 3, 2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 188, 0, 0, '');";
    echo $deletesql.'<br>';
    echo $deletevendorsql.'<br>';
    echo $vendorsql.'<br>';
}
mysql_close($spoj);
?>

