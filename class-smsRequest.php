<?php

class smsRequest
{
    public $sender;
    public $message;
    public $messageWords;


    public function setSender($sender)
    {
        $this->sender = new identifier($sender);
    }
    public function setMessage($message)
    {
        $config = config::getInstance();
        // remove whitespace and convert to lower case
        $this->message = strtolower(trim($message));
        // remove any instances of wifi from the message
        $this->message = str_replace($config->values['strip-keyword'], "", $this->
            message);

        $this->messageWords = explode(' ', $this->message);

    }

    public function security()
    {
        error_log("SMS: Security info request from $this->sender->text");
        $sms = new smsResponse;
        $sms->to = $this->sender->text;
        $sms->set_reply();
        $sms->security();

    }
    public function help()
    {
        error_log("SMS: Sending help information to $this->sender->text");
        $sms = new smsResponse;
        $sms->to = $this->sender->text;
        $sms->set_reply();
        $sms->help($this->message);
    }
    public function newPassword()
    {
        error_log("SMS: Creating new password for $this->sender->text");
        $user = new user();
        $user->identifier = $this->sender->text;
        $user->enroll(true);

    }
    public function other()
    {
        $config = config::getInstance();

        if (!$config->values['send-terms'] or $this->messageWords[0] == "agree")
        {
            error_log("SMS: Creating new account for $this->sender->text");
            $user = new user();
            $user->identifier = $this->sender->text;
            $user->enroll();
        } else
        {
            $sms = new smsResponse;
            $sms->to = $this->sender->text;
            $sms->set_reply();
            $sms->terms();
            error_log("SMS: Initial request, sending terms to $this->sender->text");
        }
    }


}

?>