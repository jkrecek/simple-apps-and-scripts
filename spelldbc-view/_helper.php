<?
require_once("functions.php");

define("DEF", 0);
define("ENUM", 1);
$effect = array(
    'MECHANIC_NONE             = 0',
    'MECHANIC_CHARM            = 1',
    'MECHANIC_DISORIENTED      = 2',
    'MECHANIC_DISARM           = 3',
    'MECHANIC_DISTRACT         = 4',
    'MECHANIC_FEAR             = 5',
    'MECHANIC_GRIP             = 6',
    'MECHANIC_ROOT             = 7',
    'MECHANIC_SLOWATTACK       = 8',                          //0 spells use this mechanic', but some SPELL_AURA_MOD_MELEE_HASTE and SPELL_AURA_MOD_RANGED_HASTE use as effect mechanic
    'MECHANIC_SILENCE          = 9',
    'MECHANIC_SLEEP            = 10',
    'MECHANIC_SNARE            = 11',
    'MECHANIC_STUN             = 12',
    'MECHANIC_FREEZE           = 13',
    'MECHANIC_KNOCKOUT         = 14',
    'MECHANIC_BLEED            = 15',
    'MECHANIC_BANDAGE          = 16',
    'MECHANIC_POLYMORPH        = 17',
    'MECHANIC_BANISH           = 18',
    'MECHANIC_SHIELD           = 19',
    'MECHANIC_SHACKLE          = 20',
    'MECHANIC_MOUNT            = 21',
    'MECHANIC_INFECTED         = 22',
    'MECHANIC_TURN             = 23',
    'MECHANIC_HORROR           = 24',
    'MECHANIC_INVULNERABILITY  = 25',
    'MECHANIC_INTERRUPT        = 26',
    'MECHANIC_DAZE             = 27',
    'MECHANIC_DISCOVERY        = 28',
    'MECHANIC_IMMUNE_SHIELD    = 29',                         // Divine (Blessing) Shield/Protection and Ice Block
    'MECHANIC_SAPPED           = 30',
    'MECHANIC_ENRAGED          = 31'
);
Form(true, POST);
echo '<p><label for="arr">From core:</label></p>';
echo '<p><textarea name="arr" cols="70" rows="13"></textarea></p>';
echo '<p><input type="submit" value="Send" /></p>';
Form(false);
//echo $_POST['arr'];
if (!isset($_POST['arr']))
    die();

$arr = $_POST['arr'];
//echo '<p>'.$arr.'</p>';
$arr_a = explode('#define', $arr);
$type = DEF;
if (sizeof($arr_a) == 1)
{
    $arr_a = explode(',', $arr);
    $type = ENUM;    
}
/*foreach($arr_a as $a)
    echo '<p>'.$a.'</p>';*/

if (empty($arr_a))
    die();

echo '<p><h2>START</h2></p>';
$count = 1;
foreach($arr_a as $line)
{
   if (!$line || empty($line) )
       continue;

   $name = "";
   $id = 0;
   if ($type == ENUM)
   {
       $nac = explode("=", $line);

       $name_a = explode(" ", $nac[0]);
       $name = $name_a[0];
       
       $id_a = explode(",", $nac[1]);
       $id = $id_a[0];       
   }
   else if ($type == DEF)
   {
       $nac = explode(" ", $line);
       for($i = 0; $i < sizeof($nac); ++$i)
       {
           $arg = $nac[$i];
           if ($arg && !empty($arg))
           {
               if (!$name)
                   $name = $arg;
               else
               {
                   $id = $arg;
                   break;
               }
           }           
       }
   }

   echo $count." => '".$name."',<br>";
   $count++;
}
echo '<p><h2>END</h2></p>';
?>