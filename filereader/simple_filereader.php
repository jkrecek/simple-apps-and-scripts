<?php
$dir = "";
$filename = isset($_GET['f']) ? $_GET['f'] : 0;

if (!$filename)
{
    if (is_dir($dir))
    {
        if ($dh = opendir($dir))
        {
            $currentFile = $argv[0]; 

            while ($file = readdir($dh))
                echo '<p><a href="'.$currentFile.'?f='.$file.'">'.$currentFile.'</a></p>'; 


            closedir($dh);
        }
    }
    else
        echo 'Wrong dir route';    
}
else
{
    echo nl2br(htmlspecialchars(file_get_contents($filename)));
}
?>