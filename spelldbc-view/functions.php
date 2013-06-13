<?php
require_once("syntax.php");

define("r", "r");
define("c", "c");
define("h", "h");
define("details", "Details");

define("GET", "get");
define("POST", "post");

define("MAX_EFFECT", 3);
define("ADV_SRCH_COLLUMN", 2);

define("NUM", 0);
define("STR", 1);
define("CHNG", 2);

function printRow($name, $value)
{
    s(r);
     s(h);
      echo $name;
     e(h);
     s(c);
      echo $value;
     e(c);
    e(r);
}

function printEffectRow($menoradku, $zacatek, $tab, $pole = 0)
{
    s(r);
     s(c);
      echo $menoradku;
     e(c);
    
     for($i = 1; $i <= MAX_EFFECT; $i++)
     {
        $id = $tab[$zacatek.$i];
        $line = $id;
        if ($pole && $id)
           $line = '<a title="'.$pole[$id].'">'.$id.'</a>';
        
        s(c); echo $line; e(c);
     }
    e(r);
}

function printEffectLink($menoradku, $zacatek, $tab, $pole = 0)
{
    s(r);
    s(c); echo $menoradku; e(c);
     
    for($i = 1; $i <= MAX_EFFECT; $i++)
    {
       $id = $tab[$zacatek.$i];
       $line = $id;
       if ($id)
       {
           if ($pole)
               $line .= ' <br> '.$pole[$id];
           $line = '<a href = ".?id='.$id.'">'.$line.'</a>';
       }
        
       s(c); echo $line; e(c);
    }
    e(r);
}

function printSameDifficulty($difid, $spoj)
{
    printRow('SpellDifficultyId', $difid);
    if ($difid)
    {
        $difsql="SELECT `m_ID`, `m_name_lang_1` as name FROM dbc_Spell WHERE `m_spellDifficultyID` = ".$difid;
        $res=mysql_query($difsql,$spoj);
        $title = 'Same difficulty:&#10;&#13;';
        $line = '';
        while ($tab=mysql_fetch_array($res))
            $line .= '<a title = "'.$tab['name'].'">'.$tab['m_ID'].'</a>'.endl;
        $val = '<a title="'.$title.'">'.$line.'</a>';
        printRow('Same DifficultyId', $line);
    }
}

function printTriggeredBy($id, $spoj)
{
    $sql="SELECT `m_ID`, `m_name_lang_1` as name FROM dbc_Spell WHERE `m_effectTriggerSpell_1` = ".$id." OR `m_effectTriggerSpell_2` = ".$id." OR `m_effectTriggerSpell_3` = ".$id;
    $res=mysql_query($sql,$spoj);
    $trigline = '';
    if (!$res || !mysql_num_rows($res))
        $trigline = 0;
    while($tab=mysql_fetch_array($res))
        $trigline .= '<a href=".?id='.$tab['m_ID'].'" title="'.$tab['name'].'">'.$tab['m_ID'].'</a><br>';

    printRow('Triggered by', $trigline);
}
function printForm($adv, $values)
{
    $isAdvanced = $adv == 'Advanced';
    foreach($values as $get_val)
    {
        if ($get_val[0] == 'id')
            continue;
        
        if ($get_val[1])
        {
            $isAdvanced = true;
            break;
        }
    }
    $val = $isAdvanced ? 'Simple' : 'Advanced';

    Table(true, 'vyhledavani');
    Form(true, GET);
    
    if (!$isAdvanced)
    {
       writeSearchSimpleLine('id', 'Spell ID:');
    }
    else
    {
        writeSearchAdvancedLine('name', 'Name:');
        writeSearchAdvancedLine('family', 'Family Name:');
        writeSearchAdvancedLine('effect', 'Effect:');
        writeSearchAdvancedLine('bp', 'Base points:');
        writeSearchAdvancedLine('aura', 'Aura:');
        writeSearchAdvancedLine('misc', 'Misc points:');
        writeSearchAdvancedLine('implA', 'Target A:');
        writeSearchAdvancedLine('miscB', 'Misc points B:');
        writeSearchAdvancedLine('implB', 'Target B:');
        writeSearchAdvancedLine('trigger', 'Triggers:');
    }
    
     s(r);
      s(c, $isAdvanced ? 4 : 2);
       echo '<input type="submit" value="OK" class="buttons" />';
       Form(false);
       Form(true,GET);
       echo '<input type="submit" name="adv" value="'.$val.'" class="buttons" />';
       Form(false);
      e(c);
     e(r);
    Table(false);
}

function printAttributeRow($name, $value)
{
    s(r);
     s(h);
      echo $name;
      if ($value)
          printAttButton($name);
     e(h);
     s(c);
      if ($value)
      {
          $hex = dechex($value);
          echo '0x'.$hex.' ('.$value.')';
          if (isset($_POST[$name]))
          {
              $arr = $GLOBALS[$name];
              global $hex;
              for($i = 0; $i < sizeof($arr); $i++)
              {
                  //echo '<p>'.$value.' '.$hex[$i].'</p>';
                  if ($value & hexdec($hex[$i]))
                      echo '<p>'.$arr[$i].'</p>';                  
              }
          }
      }
      else
          echo $value;
     e(c);
    e(r);
}

function printAttButton($name)
{
    Form(true,POST);
    echo '<input type="submit" name="'.$name.'" value="Details" class="buttons" />';
    Form(false);    
}

function buildSql($values)
{
   if ($values['id'])
       $sql = "SELECT * FROM dbc_Spell WHERE m_ID = ".$values['id'][1];
   else
   {
       $sql = "SELECT `m_ID`, `m_name_lang_1` AS name, `m_spellClassSet` AS family, `m_spellIconID` as icon, 
           `m_nameSubtext_lang_1` AS subtext FROM dbc_Spell WHERE ";
       
       $spell_arr = array();
       if ($values['name'])
           $spell_arr[] .= getArg('m_name_lang_1', $values['name'][1]);
       if ($values['family'])
           $spell_arr[] .= getArg('m_spellClassSet', $values['family'][1]);
       
       $eff_c_array = array();
       for($i = 1; $i <= MAX_EFFECT; $i++)
       {
           $eff_arr = array();
           if ($values['effect'])
               $eff_arr[] = getArg("m_effect_".$i, $values['effect'][1]);
           if ($values['aura'])
               $eff_arr[] = getArg("m_effectAura_".$i, $values['aura'][1]);
           if ($values['trigger'])
               $eff_arr[] = getArg("m_effectTriggerSpell_".$i, $values['trigger'][1]);
           if ($values['implA'])
               $eff_arr[] = getArg("m_implicitTargetA_".$i, $values['implA'][1]);
           if ($values['implB'])
               $eff_arr[] = getArg("m_implicitTargetB_".$i, $values['implB'][1]);
           if ($values['bp'])
               $eff_arr[] = getArg("m_effectBasePoints_".$i, $values['bp'][1]);
           if ($values['misc'])
               $eff_arr[] = getArg("m_effectMiscValue_".$i, $values['misc'][1]);
           if ($values['miscB'])
               $eff_arr[] = getArg("m_effectMiscValueB_".$i, $values['miscB'][1]);
           
           if (!empty($eff_arr))
               $eff_c_array[] = '('.implode(' AND ', $eff_arr).')';
       }
       if (!empty($eff_c_array))
           $spell_arr[] = '('.implode(' OR ', $eff_c_array ).')';
   
       $sql .= implode(' AND', $spell_arr);
   }

   return $sql;
}

function getArg($col, $val)
{
    $adj_val = mysql_real_escape_string($val);
    $sql_arg = " = ";
    $sql_val = $adj_val;
    if (is_string($val))
    {
        $sql_arg = " LIKE ";
        $sql_val = "'".$adj_val."'";
    }
    
    return '`'.$col.'`'.$sql_arg.$sql_val;    
}

function fillIntoGetList($name, $valtype)
{
   global $values;

   if (!isset($_GET[$name]))
       return;

   $val = $_GET[$name];
   if (!$val)
       return;

    $isOk = true;
    switch($valtype)
    {
        case NUM:
        {
             if (!is_numeric($val))
                 $isOk = false;
             break;
        }
        case STR:
        {
             if (!is_string($val))
                 $isOk = false;
             break;
        }
        case CHNG:
        {
             if (is_numeric($val))
                 break;
                  
             $idx = tryToFindMatchingNumber($name, $val);
             if ($idx)
                 $val = $idx;
             else
                 $isOk = false;
             break;
        }
   }
   if (!$isOk)
       die('VYPADNI!');

   $values[$name] = array($name,$val);
}

function writeSearchSimpleLine($name, $lbl)
{
    global $values;
    s(r);
     s(c);
      echo '<label for="'.$name.'">'.$lbl.'</label>';
     e(c);
     s(c);
      echo '<input name="'.$name.'" id="'.$name.'" type="text" value="'.$values[$name][1].'" />';
     e(c);
    e(r);
}

function writeSearchAdvancedLine($name, $lbl)
{
    global $cell_counter;
    global $values;
    if (!($cell_counter%ADV_SRCH_COLLUMN))
        s(r);
    
    s(c);
    echo '<label for="'.$name.'">'.$lbl.'</label>';
    e(c);s(c);
    echo '<input name="'.$name.'" id="'.$name.'" type="text" value="'.$values[$name][1].'" />';
    e(c);
    
    
    if (!(++$cell_counter%ADV_SRCH_COLLUMN))
        e(r);
}

function tryToFindMatchingNumber($name, $value)
{
    $arr = $GLOBALS[$name];
    $idx = array_search($value, $arr);
    return $idx;
}

function GetIcon($id, $spoj)
{
    $sql="SELECT icon FROM dbc_SpellIcon WHERE `id` = ".$id;
    $res=mysql_query($sql,$spoj);
    if (!$res || !mysql_num_rows($res))
        return mysql_error();
    
    $fullName = mysql_result($res, 0, 0);
    $n_array = explode('\\', $fullName);
    $name = $n_array[2].'.png';
    return '<span title='.$id.'><img src="Icons/'.$name.'"></span>';
}
?>
