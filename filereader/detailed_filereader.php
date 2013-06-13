<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>

<?php

$dir = "";
$filetype = "";
$filename_prefix = "";
$filename = isset($_GET['f']) ? $_GET['f'] : 0;

function isFilenameOK($_file)
{
    global $filetype;
    if (is_dir($_file))
        return false;
    
    $_part = explode('.', $_file);
    if (count($_part) != 2)
        return false;
    
    return $_part[1] == $filetype;
}

if (!$filename)
{
    if (is_dir($dir))
    {
        if (opendir($dir))
        {
            $currentFile = $argv[0];

            $files = array();
            while (($file = readdir()) !== false)
            {
                $a = explode("filename_prefix", $file);
                $datepart = explode(".", $a[1]);
                if (isFilenameOK($file))
                    $files[$datepart[0]] = $file;
                else if (!is_dir($file))
                    $files[$datepart[0]] = "";
            }
            ksort($files);
            foreach($files as $date => $name)
            {
                if ($name)
                    echo '<a href="'.$currentFile.'?f='.$name.'">';
                echo $date;
                if ($name)
                    echo '</a>';
                echo '<br>';
            }
           
            closedir();
        }
    }
    else
        echo 'Wrong dir route';    
}
else
{
    if (!isFilenameOK($filename))
        die('Noup, you wont hack this!');
    
    echo nl2br(htmlspecialchars(file_get_contents($dir.$filename)));
}
?>

    </body>
</html>