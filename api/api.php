<?php

require ("../common.php");

$aaa = new aaa($_SERVER['SCRIPT_NAME']);
header("Content-Type: application/json");
header($aaa->responseHeader);


print $aaa->responseBody;

?>