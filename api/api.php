<?php

require ("../common.php");

$aaa = new aaa($_SERVER['SCRIPT_NAME']);
$aaa->reqeustJson = file_get_contents('php://input');
$aaa->processRequest();

header("Content-Type: application/json");
header($aaa->responseHeader);


print $aaa->responseBody;

?>