<?php

class email_address
{
    public $address;

    public function __constructor($address)
    {
        $this->$address = fixupemailaddress($address);

    }

    public function fixupemailaddress($address)
    {
        preg_match_all('/[A-Za-z0-9\_\+\.\'-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+/', $emailFrom,
            $matches);
        $this->emailFrom = strtolower($matches[0][0]);
    }

    public function isvalid()
    {
        return filter_var($user, FILTER_VALIDATE_EMAIL);
    }
}

?>