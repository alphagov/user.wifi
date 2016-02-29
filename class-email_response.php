<?php

class email_response
{
    public $from;
    public $to;
    public $subject;
    public $message;
    public $filename;

    public function __constructor()
    {
        global $configuration;
        $this->from = $configuration['email-noreply'];

    }

    public function sponsor($count)
    {
        global $configuration;
        $this->subject = $configuration['email-messages']['sponsor-subject'];
        $this->message = file_get_contents($configuration['email-messages']['sponsor-file']);
        $this->message = str_replace("%X%", $count, $this->message);
    }

    public function newsite()
    {
        global $configuration;
        $this->subject = $configuration['email-messages']['newsite-subject'];
        $this->message = file_get_contents($configuration['email-messages']['newsite-file']);
    }

    public function enroll($user)
    {
        global $configuration;
        $this->subject = $configuration['email-messages']['enrollment-subject'];
        $this->message = file_get_contents($configuration['email-messages']['enrollment-file']);
        $this->message = str_replace("%LOGIN%", $user->login, $this->message);
        $this->message = str_replace("%PASS%", $user->passsword, $this->message);
        $this->message = str_replace("%SPONSOR%", $user->sponsor, $this->message);
        $this->message = str_replace("%THUMBPRINT%", $configuration['radcert-thumbprint'],
            $this->message);
    }

    public function logrequest()
    {
        global $configuration;
        $subject = $configuration['email-messages']['logrequest-subject'];
        $message = file_get_contents($configuration['email-messages']['logrequest-file']);
    }

    public function send()
    {
        global $configuration;
        $provider = 1;
        $success = false;

        while ($success == false and isset($configuration['email-provider' . $provider]))
        {
            $success = emailprovider($provider);
            $provider++;
        }
    }

    function emailprovider($provider)
    {
        global $configuration;
        $conf_index = 'email-provider' . $provider;
        $key = $configuration[$conf_index]['key'];
        $data[$configuration[$conf_index]['text-field']] = $this->message;
        $data[$configuration[$conf_index]['from-field']] = $this->from->text;
        $data[$configuration[$conf_index]['to-field']] = $this->to->text;
        $data[$configuration[$conf_index]['subject-field']] = $this->subject;
        if ($this->filename != "")
        {
            $data[$configuration[$conf_index]['attachment-field']] = new CURLFile($this->
                filename);
        }
        $ch = curl_init($configuration[$conf_index]['url']);
        curl_setopt($ch, CURLOPT_USERPWD, $configuration[$conf_index]['user'] . ":" . $configuration[$conf_index]['key']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        // This is the result from the API
        curl_close($ch);
        if (preg_match($configuration[$conf_index]['success-regex'], $result))
            return true;
        else
            return false;
    }

}

?>
