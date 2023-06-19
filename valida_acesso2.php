<?php
//https://www.php.net/manual/pt_BR/function.setcookie.php#125241
//https://github.com/contao/contao/issues/1760

if(!isset($_SESSION["usuario"])) {
    $erros = ["Acesso não permitido. Favor efetuar o login!"];
    $_SESSION["erros"] = $erros;
    header("HTTP 1/1 302 Redirect");
    header("Location: login.php");
    exit();
}