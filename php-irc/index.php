<?php
ini_set("display_errors", 0);
if ($sendmsg = $_POST['send'])
{
    $linetofile = ';newMsg;#valhalla: '.$sendmsg;
    file_put_contents('msgBuffer.txt', $linetofile, (FILE_USE_INCLUDE_PATH | FILE_APPEND));
    echo 'zprava odeslana, klikni <a href=".">ZDE</a> pro navrat na zpravy';
    return;
}
echo '
 <table class="vyhledavani">
   <tr><td>
     <form action="" method="post">
      <label for="send">Message:</label>
      <input name="send" id="send" type="text" value="" />
      <input type="submit" value="OK" />
     </form>
   </td></tr>
</table>';

echo nl2br(htmlspecialchars(file_get_contents("msgHistory.txt")));

?>