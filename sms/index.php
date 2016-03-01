<?php

require ("../common.php");

$smsReq = new smsRequest();

if (isset($_REQUEST['source']))
    $smsReq->setSender($_REQUEST['source']);
else
    $smsReq->setSender($_REQUEST["sender"]);

if (isset($_REQUEST["message"]))
    $smsReq->setMessage($_REQUEST["message"]);
else
    $smsReq->setMessage($_REQUEST["content"]);


if ($smsReq->sender->validMobile)
{

    switch ($smsreq->messageWords[0])
    {
        case "security":

            $smsreq->security();
            break;

        case "new":
            $smsReq->newPassword();
            break;

        case "help":
            $smsReq->help();
            break;

        default:
            $smsReq->other();
            break;

    }


} else
{
    error_log("SMS: Invalid number " . $smsReq->sender->text);
}

?>