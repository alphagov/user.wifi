<?php

require ("../common.php");

$emailreq = new emailRequest();

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    error_log("AWS SNS EMAIL: From : " . $data['mail']['commonHeaders']['from'][0]);
    $emailreq->setEmailFrom($data['mail']['commonHeaders']['from'][0]);
    $emailreq->setEmailTo($data['mail']['commonHeaders']['to'][0]);
    $emailreq->setEmailSubject($data['mail']['commonHeaders']['Subject']);
    $emailreq->setEmailBody($data['Content']);


switch ($emailreq->emailToCMD)
{
    case "enroll":
        $emailreq->enroll();
        break;
    case "enrol":
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
