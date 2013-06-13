<?php
ini_set("display_errors", 1);
require_once('functions.php');

session_start();
//$u = $_SESSION['user'];
//$h = $_SESSION['passhash'];
//echo $u.'           '.$h;
if (!isset($_SESSION['user']))
{
    $error = '';
    if (isset($_POST['user']) && isset($_POST['pass']))
    {
        if (doMatchPassForUser($_POST['user'], sha1($_POST['pass'])))
        {
            $_SESSION['user'] = $_POST['user'];
            echo 'Logged in succesfully. <a href=".">continue</a>';
            exit();
        }
        $error = "WRING PASS?";
    }
    Form(true, POST);
    Table(true);
    s(r); s(c); echo '<b>LOGIN: </b>'; e(c);e(r);
    if ($error)
         s(r); s(c, 2); echo $error; e(c);e(r);
    s(r); s(c); echo '<label for="u">Uzivatelske jmeno:</label>'; e(c);s(c); echo '<input type="text" id="u" name="user">'; e(c);e(r);
    s(r); s(c); echo '<label for="p">Heslo:</label>'; e(c);s(c); echo '<input type="password" id="p" name="pass">'; e(c);e(r);
    s(r); s(c); echo '<input type="submit" value="Odeslat">'; e(c); e(r);
    Table(false);
    Form(false);
    exit();
}

$cpp = isset($_GET['cpp']) ? $_GET['cpp'] : '';

Form(true, GET);
Table(true);
s(r); s(c); echo '<b>Source code: </b>'; e(c);e(r);
s(r); s(c); echo '<textarea name="cpp" cols=100 rows=20>'.$cpp.'</textarea>'; e(c);e(r);
s(r); s(c); echo '<input type="submit" value="Odeslat">'; e(c); e(r);
Table(false);
Form(false);

if ($cpp)
{
    $output = execute($cpp);
    Table(true);
    s(r); s(c); echo '<b>Output: </b>'; e(c);e(r);
    s(r); s(c); echo $output; e(c);e(r);
    Table(false);
}

?>