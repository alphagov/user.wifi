<?php

class sms_request
{
    public $sender;
    public $message;
    public $message_words;

    public function __construct()
    {
        global $configuration;
        if (isset($_REQUEST['source']))
            $this->sender = new identifier($_REQUEST['source']);
        else
            $this->sender = new identifier($_REQUEST["sender"]);

        // Message body
        if (isset($_REQUEST["message"]))
            $this->message = $_REQUEST["message"];
        else
            $this->message = $_REQUEST["content"];

        // remove whitespace and convert to lower case
        $this->message = strtolower(trim($this->message));
        // remove any instances of wifi from the message
        $this->message = str_replace($configuration['strip-keyword'], "", $this->
            message);

        $this->message_words = explode(' ', $this->message);

    }
    
    


}

?>