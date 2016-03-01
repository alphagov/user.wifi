<?php

require ("../common.php");

$emailreq = new emailRequest();
$emailreq->setEmailFrom($_REQUEST['sender']);
$emailreq->setEmailTo($_REQUEST['recipient']);
$emailreq->setEmailSubject($_REQUEST['subject']);
$emailreq->setEmailBody($_REQUEST['body-plain']);

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
