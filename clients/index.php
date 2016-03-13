<?php

require ("../common.php");

$db = DB::getInstance();
$dblink = $db->getConnection();
$handle = $dblink->prepare('select shortname, nasname, secret from nas');
$handle->execute();
while ($result = $handle->fetch(PDO::FETCH_ASSOC))
{
    print 'client '.$result['shortname'] . ' {
        ipaddr = ' . $result['nasname'] . '
        secret = ' .
        $result['secret'] . '
        }
';
}

?>