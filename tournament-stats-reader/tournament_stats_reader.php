<?php
$maps = array(
  559 => 'Nagrand Arena',
  562 => 'Blade\'s Edge Arena',
  572 => 'Ruins of Loardaeron',
  617 => 'Dalaran Sewers',
  618 => 'The Ring of Valor',
);
?>

<?xml version="1.0" encoding="windows-1250">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs">

<head>
 	<title>Valhalla Arena Tournament</title>
  <meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
  <meta http-equiv="content-language" content="cs" />
</head>
<body>

<?php	
    $spoj=mysql_connect("host","user","pass"); //server

    mysql_select_db("dbname",$spoj); //server
    mysql_query("SET CHARSET cp1250");

    function getArenaTeamName($guid)
    {
       $sql="SELECT name FROM arena_team WHERE arenateamid = $guid";
       $vys=mysql_query($sql,$spoj);
       return mysql_result($vys, 0);
    }


    $sql="SELECT guid, FROM_UNIXTIME(endtime), winnerid, loserid, dmgdone, healdone, mapid, duration FROM arena_stats;";
    $vys=mysql_query($sql,$spoj);
    
    echo '<table>';
    while ($tab=mysql_fetch_array($vys))
    {
        echo '
        <tr>
            <td>'.$tab['FROM_UNIXTIME(endtime)'].'</td>
            <td>'.getArenaTeamName($tab['winnerid']).'</td>
            <td>'.getArenaTeamName($tab['loserid']).'</td>
            <td>'.$tab['dmgdone'].'</td>
            <td>'.$tab['healdone'].'</td>
            <td>'.$maps($tab['healdone']).'</td>
            <td>'.$tab['duration'].'</td>
            <td><a href = details.php?id='.$tab['guid'].'>Detaily</a></td>
        </tr>
       ';           
    };
    echo '</table>';

    mysql_close($spoj);
?>
</body>
</html>
