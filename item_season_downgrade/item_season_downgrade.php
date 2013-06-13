<?php
define("DB_LOGIN_NAME", );
define("DB_LOGIN_PASS", );
define("DB_WORLD_TABLE", );
define("DB_CHAR_TABLE", );

function printLine($line)
{
   echo '<p>'.$line.'</p>';
}

function query($sql, $spoj)
{
   if ($q = mysql_query($sql, $spoj))
       return $q;
   
   printLine("Error in SQL '".$sql."': ".mysql_error());
   return NULL;
}

$time_start = microtime(true);

echo '<html>
<head>
 	<title>Season degradator</title>
  <meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
  <meta http-equiv="content-language" content="cs" />
</head>
<body>';

$spoj=mysql_connect("localhost", DB_LOGIN_NAME ,DB_LOGIN_PASS); //server
if (!$spoj)
     die("Could not conected to mysql: ".mysql_error());


/****** World DB part ******/
mysql_select_db(DB_WORLD_TABLE,$spoj);

$names = array(6 => "Furious", 7 => "Relentless", 8 => "Wrathful");

$items = array(array(array()));
$unique = array();
for($season = 6; $season <= 8; ++$season)
{
   printLine("<b><font color=red>Woking on season ".$season."</font></b>");

   $step_time = microtime(true);
   $seasonname = $names[$season];
   $sql="SELECT entry, name, maxcount FROM `item_template` WHERE `name` LIKE '".$seasonname."%' AND `RequiredLevel` = 80";
   $vys=query($sql,$spoj);
    
   if (!mysql_num_rows($vys))
        die("No items for season ".$season." found!");

   while ($tab=mysql_fetch_array($vys))
   {
       $entry = $tab['entry'];
       $name = $tab['name'];

       $name_array = explode(" Gladiator's ", $name);
       if ($name_array[0] != $seasonname)
       {
           printLine("WARNING: Item '".$name."' (ID: ".$entry.") is not season item, is that correct?");
           continue;
       }

       $items[$name_array[1]][$season] = $entry;
       if ($tab['maxcount'] != 0)
           $unique[] = $entry;
   }
   $step_length = microtime(true)-$step_time;
   printLine("<font color=green>Done, items for season ".$season." loaded in ".$step_length."</font>");
}
/****** custom items ******/
$items['Medallion of the Horde'][6] = 42126;
$items['Medallion of the Horde'][8] = 51378;

$items['Medallion of the Alliance'][6] = 42124;
$items['Medallion of the Alliance'][8] = 51377;

$items['Battlemaster crit'][6] = 42128;
$items['Battlemaster crit'][7] = 42133;

$items['Battlemaster hit'][6] = 42129;
$items['Battlemaster hit'][7] = 42134;

$items['Battlemaster ap'][6] = 42131;
$items['Battlemaster ap'][7] = 42136;

$items['Battlemaster sp'][6] = 42132;
$items['Battlemaster sp'][7] = 42137;

$items['Battlemaster haste'][6] = 42130;
$items['Battlemaster haste'][7] = 42135;

$items['Band of Ascendancy'][6] = 42116;
$items['Band of Victory'][6] = 42117;
$items['Blade of Alacrity'][6] = 42347;
$items['Blade of Celerity'][6] = 42347;
$items['Compendium'][6] = 42526;
$items['Cord of Alacrity'][6] = 41898;
$items['Cuffs of Alacrity'][6] = 41909;
$items['Treads of Alacrity'][6] = 41635;
$items['Treads of Dominance'][6] = 41635;
$items['Treads of Salvation'][6] = 41621;
$items['Wand of Alacrity'][6] = 42503;
$items['Tabard'][6] = 45983;


/****** Char DB part ******/
printLine("<b><font color=red>Updating database</font></b>");

mysql_select_db(DB_CHAR_TABLE,$spoj);
$step_time = microtime(true);
foreach($items as $key => $ids)
{
   // check if all alternatives were found
   if (!isset($ids[6]) || (!isset($ids[7]) && !isset($ids[8])))
   {
       $error_line = "ERROR: Some item alternatives for category '".$key."' are missing:";
       for($season = 6; $season <= 8; ++$season)
           $error_line .= " S".$season." Id: ".(isset($ids[$season]) ? $ids[$season] : 0);
       printLine($error_line);
       continue;
   }
   
   $id6 = $ids[6];

   // change ids to s6 items
   $updatesql = "UPDATE item_instance SET itemEntry = ".$id6." WHERE itemEntry IN (";
   if (isset($ids[7]))
   {
       $updatesql .= $ids[7];
       if (isset($ids[8]))
           $updatesql .= ", ".$ids[8];
   }
   else if (isset($ids[8]))
       $updatesql .= $ids[8];

   $updatesql .= ")";
   query($updatesql,$spoj);

   // check if item is unique, if not go to next item
   if (!in_array($id6, $unique))
       continue;

   // find unique duplicity
   $selectres = query("SELECT guid,owner_guid FROM item_instance WHERE itemEntry = ".$id6, $spoj);
   if (!$selectres)
       continue;

   $itemdupl = array(array());
   while ($tab=mysql_fetch_array($selectres))
       $itemdupl[$tab['owner_guid']][] = $tab['guid'];

   $deletecount = 0;
   foreach($itemdupl as $owner)
   {
       $itemcount = count($owner);
       if ($itemcount == 1)
           continue;

       $onekept = false;
       foreach($owner as $itemguid)
       {
           if (!$onekept)
           {
               $onekept = true;
               continue;
           }
           else
           {
               query("DELETE FROM item_instance WHERE guid = ".$itemguid,$spoj);
               $deletecount++;
           }
       }
   }
   if ($deletecount)
       printLine("Deleted ".$deletecount." items for entry ".$id6);
}
$step_total = microtime(true)-$step_time;

printLine("<font color=green>Done, database updated in ".$step_total."</font>");

printLine("<b><font color=red>Cleanuing up character_inventory</font></b>");

$step_time = microtime(true);
query("DELETE FROM character_inventory WHERE item not in (SELECT guid FROM item_instance)",$spoj);
$step_length = microtime(true)-$step_time;

printLine("<font color=green>Done, table cleaned-up in ".$step_length."</font>");

$time_end = microtime(true);
$length = $time_end-$time_start;

printLine("<font color=red><b>Script complete in ".$length." seconds</b></font>");

echo '</body>
</html>';
?>
