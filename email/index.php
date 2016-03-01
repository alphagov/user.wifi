<?php

require ("../common.php");

$emailreq = new emailRequest();
switch ($emailreq->emailToCMD)
{
    case "enroll":
        error_log("Email: Self enrollment request from $emailreq->sender->text");
        $emailreq->enroll();
        break;
    case "sponsor":
        error_log("Email: Sponsored enrollment request from $emailreq->sender->text");
        $emailreq->sponsor();
        break;
    case "newsite":
        error_log("Email: New site request request from $emailreq->sender->text");
        $emailreq->newsite();
        break;
    case "logrequest":
        error_log("Email: Log request from $emailreq->sender->text");
        $emailreq->logrequest();
        break;
}

?>
