<?php

$path = preg_replace('~^/api/~', '', $_SERVER['SCRIPT_NAME']);
$parts = explode('/', $path);
var_dump($parts);

?>