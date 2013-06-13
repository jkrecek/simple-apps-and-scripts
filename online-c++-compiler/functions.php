<?php
require_once('syntax.php');
require_once('defines.php');

function execute($cpp)
{
   $file = fopen('test.cpp', 'w');
   fwrite($file, HEADERS);
   $bytes = fwrite($file, $cpp);
   if (!$bytes)
       return 'ERROR: Failed to write into file';

   system('./compile');
   system('./bin > log.log');

   return nl2br(htmlspecialchars(file_get_contents("log.log")));
}

function doMatchPassForUser($user, $pass)
{
   switch($user)
   {
      case "test": return $pass == '4c76825badccb9869e4e682b2836b81348a93dc8';
      default: return false;
   }
}

?>