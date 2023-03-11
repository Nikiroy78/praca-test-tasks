<?php
/* Отключим WARNING */
error_reporting(E_ERROR | E_PARSE);
include "method.php";

die(output($_GET['root']));
?>