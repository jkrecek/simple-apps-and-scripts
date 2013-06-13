<?php
$spoj=mysql_connect("host","user","pass");

mysql_select_db("dbname",$spoj);
mysql_query("SET CHARSET cp1250");

function randomString()
{
   $chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z".
      "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z".
      "0","1","2","3","4","5","6","7","8","9");

      $str = '';
   for ($a = 0; $a < 50; $a++)
      $str .= $chars[rand(0,60)];

   return $str;
}

for ($i = 0; $i < 11500; $i++)
{
    $sql="UPDATE mybb_users SET loginkey='".randomString()."' WHERE uid=".$i.";";
    echo $sql."<br>";
    mysql_query($sql,$spoj);   
}
?>