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


if ($smsReq->sender->valid_mobile)
{
    $sms = new smsResponse;
    $sms->to = $smsReq->sender->text;
    $sms->set_reply();

    switch ($smsreq->message_words[0])
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
    error_log("SMS: Invalid number $smsreq->sender->text");


}

?>