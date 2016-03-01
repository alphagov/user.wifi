<?php

require ("../common.php");
// Load the configuration file into a global variable
loadconfiguration();
// Connect to the database
db_connect();
$emailreq = new emailRequest();
switch ($emailreq->emailToCMD) {
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
