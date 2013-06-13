<?php
require_once("arrays.php");
require_once("functions.php");
require_once("syntax.php");

$time_start = microtime(true);

define("DB_HOST", "");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "");

$values = array();
fillIntoGetList('id', NUM);

fillIntoGetList('name', STR);
fillIntoGetList('family', CHNG);
fillIntoGetList('effect', CHNG);
fillIntoGetList('aura', CHNG);
fillIntoGetList('trigger', NUM);
fillIntoGetList('implA', CHNG);
fillIntoGetList('implB', CHNG);
fillIntoGetList('bp', NUM);
fillIntoGetList('misc', NUM);
fillIntoGetList('miscB', NUM);

$adv = isset($_GET['adv']) ? $_GET['adv'] : 'Simple';
$pageid = $values['id'][1];

$cell_counter = 0;

// start printing
Html(true);
Head('Spell Details - '.$pageid);
Body(true);

printForm($adv, $values);

$die = true;
foreach($values as $i)
  if ($i[1])
   $die = false;
      
if ($die)
    die('');

$spoj=mysql_connect(DB_HOST,DB_USER,DB_PASS); //server

mysql_select_db(DB_NAME,$spoj);               //db
mysql_query("SET CHARSET cp1250");

$sql=buildSql($values);
$vys=mysql_query($sql,$spoj);

if (!$vys || !mysql_num_rows($vys))
    die('<center><h2>No spell found!</h2></center>'.mysql_error());

if ($pageid)
{
    while ($tab=mysql_fetch_array($vys))
    {
        Table(true, 'base_table');
        s(r);
         s(c);Table(true, 'spell_table');
        
        s(r);s(h,2);echo '<h2>Spell Info</h2>'; e(h);e(r);
        printRow('Icon', GetIcon($tab['m_spellIconID'], $spoj));
        printRow('ID', $tab['m_ID']);
        printRow('Name', $tab['m_name_lang_1']);
        printRow('Description', $tab['m_description_lang_1']);
        printRow('Subtext', $tab['m_nameSubtext_lang_1']);
        printRow('Dispel', $tab['m_dispelType']);
        printRow('Category', $tab['m_category']);
        $mch = $tab['m_mechanic'];
        printRow('Mechanic', '<a title="'.$mechanic[$mch].'">'.$mch.'</a>');
        printAttributeRow('Attributes', $tab['m_attributes']);
        printAttributeRow('AttributesEx', $tab['m_attributesEx']);
        printAttributeRow('AttributesEx2', $tab['m_attributesExB']);
        printAttributeRow('AttributesEx3', $tab['m_attributesExC']);
        printAttributeRow('AttributesEx4', $tab['m_attributesExD']);
        printAttributeRow('AttributesEx5', $tab['m_attributesExE']);
        printAttributeRow('AttributesEx6', $tab['m_attributesExF']);
        printAttributeRow('AttributesEx7', $tab['m_attributesExG']);
        printRow('InterruptFlags', $tab['m_auraInterruptFlags']);
        printRow('Speed', $tab['m_speed']);
        //printRow('SpellIconID', $tab['m_spellIconID']);
        $fam = $tab['m_spellClassSet'];
        printRow('SpellFamilyName', $fam.' - '.$family[$fam]);
        printRow('SpellFamilyFlags0', $tab['m_spellClassMask_1']);
        printRow('SpellFamilyFlags1', $tab['m_spellClassMask_2']);
        printRow('SpellFamilyFlags2', $tab['m_spellClassMask_3']);
        printRow('MaxAffectedTargets', $tab['m_maxTargets']);
        printSameDifficulty($tab['m_spellDifficultyID'], $spoj);
        printTriggeredBy($tab['m_ID'], $spoj);

        Table(false);e(c);
        s(c);Table(true, 'effect_table');
        s(r);s(h,4);echo '<h2>Effect Info</h2>'; e(h);e(r);
        s(r);
         s(c); echo '<h3>Effect info</h3>'; e(c);
         s(c); echo '<h3>Effect 0</h3>'; e(c);
         s(c); echo '<h3>Effect 1</h3>'; e(c);
         s(c); echo '<h3>Effect 2</h3>'; e(c);
        e(r);
        
        printEffectRow("Effect", "m_effect_", $tab, $effect);
        printEffectRow("EffectBasePoints", "m_effectBasePoints_", $tab);
        printEffectRow("EffectMechanic", "m_effectMechanic_", $tab, $mechanic);
        printEffectRow("EffectImplicitTargetA", "m_implicitTargetA_", $tab, $implA);
        printEffectRow("EffectImplicitTargetB", "m_implicitTargetB_", $tab, $implB);
        printEffectRow("EffectRadiusIndex", "m_effectRadiusIndex_", $tab);
        printEffectRow("EffectApplyAuraName", "m_effectAura_", $tab, $aura);
        printEffectRow("EffectAmplitude", "m_effectAuraPeriod_", $tab);
        printEffectRow("EffectMultipleValue", "m_effectAmplitude_", $tab);
        printEffectRow("EffectMiscValue", "m_effectMiscValue_", $tab);
        printEffectRow("EffectMiscValueB", "m_effectMiscValueB_", $tab);
        printEffectLink("EffectTriggerSpell", "m_effectTriggerSpell_", $tab);

        Table(false);
        e(c);e(r);Table(false);
    }
}
else
{
    $num_rows = mysql_num_rows($vys);
    echo '<p><center><h2>Found '.$num_rows.' spells</h2></center></p>';
    Table(true, 'search_table');
    s(r);
     s(h);echo 'Icon';e(h);
     s(h);echo 'Id';e(h);
     s(h);echo 'Name';e(h);
     s(h);echo 'Family Name';e(h);
     s(h);echo 'Subtext';e(h);
    e(r);
    
    while ($tab=mysql_fetch_array($vys))
    {
        s(r);
         s(c); echo GetIcon($tab['icon'], $spoj); e(c);
         s(c); echo '<a href=".?id='.$tab['m_ID'].'">'.$tab['m_ID']; e(c);
         s(c); echo $tab['name']; e(c);
         s(c); echo $family[$tab['family']]; e(c);
         s(c); echo $tab['subtext']; e(c);
        e(r);
    }
    
    Table(false);
}

mysql_close($spoj);

$time_end = microtime(true);
$time = $time_end - $time_start;
echo '<small><p>Vygenerováno za '.$time.' sekund</p></small>';

Body(false);
Html(false);
?>