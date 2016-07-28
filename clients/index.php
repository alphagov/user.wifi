<?php

if ($_REQUEST['key'] == "xp93rDXY65DKQ5IiKlUC0sN0WDwj0v")
{


    require ("../common.php");

    $db = DB::getInstance();
    $dblink = $db->getConnection();
    $handle = $dblink->prepare('select ip, radkey from site, siteip where site.id = siteip.site_id;');
    $handle->execute();

    while ($result = $handle->fetch(PDO::FETCH_ASSOC))
    {
        $clientName = str_replace('.', '-', $result['ip']);
        $clientIp = $result['ip'];
        $clientSecret = $result['radkey'];
        print 'client ' . $clientName . ' {
        ipaddr = ' . $clientIp . '
        secret = ' . $clientSecret . '
        }
';
    }
}

?>