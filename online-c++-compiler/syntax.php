<?php
define("endl", "<br>");

function Html($start)
{
    if ($start)
    {
      echo '
       <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
       <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs">';
    }
    else
        echo '</html>';
}

function Head($title)
{
  echo '
   <head>
    <title>'.$title.'</title>
     <link rel="stylesheet" type="text/css" href="style.css">
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
    <meta http-equiv="content-language" content="cs" />
   </head>';
}

function Body($start)
{
    echo $start ? '<body>' : '</body>';
}

function Table($start, $class = '')
{
    if ($start)
    {
        if ($class)
            echo '<table class="'.$class.'">';
        else
            echo '<table>';
    }
    else
        echo '</table>';
}

function Form($start, $type = '')
{
    if ($start)
        echo '<form action="" method="'.$type.'">';
    else
        echo '</form>';
}

function s($t, $colspan = 0)
{
    $span_line = $colspan ? ' colspan='.$colspan : '';
    if ($t == c)
        echo '<td'.$span_line.'>';
    elseif ($t == r)
        echo '<tr'.$span_line.'>';
    elseif ($t == h)
        echo '<th'.$span_line.'>';
}

function e($t)
{
   if ($t == c)
       echo '</td>';
   elseif ($t == r)
       echo '</tr>';
   elseif ($t == h)
       echo '</th>';
}
?>
