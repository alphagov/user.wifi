<?php
require ("../common.php");

$aaa = new aaa($_SERVER['SCRIPT_NAME']);

 header("HTTP/1.1 200 OK");
 header("Content-Type: application/json");
 $response['control:Cleartext-Password']= $aaa->user->password;
 $response['reply:Reply-Message']="";
 $json = json_encode($reponse);
 print $json;



?>