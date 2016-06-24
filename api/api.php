<?php

require ("../common.php");

$aaa = new aaa($_SERVER['SCRIPT_NAME']);
$aaa->requestJson = file_get_contents('php://input');
$aaa->processRequest();
header($aaa->responseHeader);
header("Content-Type: application/json");



print $aaa->responseBody;

?>