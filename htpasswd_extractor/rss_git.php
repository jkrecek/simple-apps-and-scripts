<?php

$parameters = isset($_GET['parameters']) ? $_GET['parameters'] : '';
$host = "";
$user = "";
$pass = "";

function printForm()
{
    global $parameters;
    global $host;
    echo '<form action="" method="get">';
       echo '<label for="parameters">'.$host.'?</label>  <input name="parameters" type="text" value="'.$parameters.'" />';
       echo '<input type="submit" value="Request" />';
    echo '</form>';
}


if ($parameters)
{
    $curl = curl_init($host.'?'.$parameters);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_PORT , 80);
    curl_setopt($curl, CURLOPT_USERPWD, $user.':'.$pass);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $response = curl_exec($curl);
    $resultStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    echo $resultStatus;
    /*if ($resultStatus == 200)
        echo $response;
    else
        printForm();*/
}
else
    printForm();
?>