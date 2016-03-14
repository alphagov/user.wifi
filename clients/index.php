<?php

require ("../common.php");

$db = DB::getInstance();
$dblink = $db->getConnection();
$handle = $dblink->prepare('select nasname, secret from nas');
$handle->execute();
$clientName = str_replace('.','-',$result['nasname']);
$clientIp = $result['nasname'];
$clientSecret = $result['secret'];
while ($result = $handle->fetch(PDO::FETCH_ASSOC))
{
    print 'client '. $clientName . ' {
        ipaddr = ' . $clientIp . '
        secret = ' .
        $clientSecret . '
        }
';
}

?>