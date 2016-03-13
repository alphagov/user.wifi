<?php

require ("../common.php");

$db = DB::getInstance();
$dblink = $db->getConnection();
$handle = $dblink->prepare('select shortname, nasname, secret from nas');
$handle->execute();
while ($result = $handle->fetch(PDO::FETCH_ASSOC))
{
    print $result['shortname'] . ' {\nipaddr = ' . $result['nasname'] . '\nsecret = ' .
        $result['secret'] . '\n}';
}

?>