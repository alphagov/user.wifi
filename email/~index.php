<?php

require ("../common.php");

$emailreq = new emailRequest();

if (isset($_REQUEST['json']))
{
    $data = json_decode(stripslashes($_REQUEST['json']), true);
    // Support for Postmark
    error_log("EMAIL: From : " . $data->from);
    $emailreq->setEmailFrom($data->from);
    $emailreq->setEmailTo($data->to);
    $emailreq->setEmailSubject($data->subject);
    $emailreq->setEmailBody($_REQUEST['body-plain']);
}

if (isset($_REQUEST['sender']))
{
    // Support for Mailgun
    error_log("EMAIL: From : " . $_REQUEST['sender']);
    $emailreq->setEmailFrom($_REQUEST['sender']);
    $emailreq->setEmailTo($_REQUEST['recipient']);
    $emailreq->setEmailSubject($_REQUEST['subject']);
    $emailreq->setEmailBody($_REQUEST['body-plain']);
}


switch ($emailreq->emailToCMD)
{
    case "enroll":
        $emailreq->enroll();
        break;
    case "sponsor":
        $emailreq->sponsor();
        break;
    case "newsite":
        $emailreq->newsite();
        break;
    case "logrequest":

        $emailreq->logrequest();
        break;
}

?>
