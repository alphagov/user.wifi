<?php

class emailResponse
{
    public $from;
    public $to;
    public $subject;
    public $message;
    public $filename;

    public function __constructor()
    {
        $config = config::getInstance();
        $this->from = $config->values['email-noreply'];

    }

    public function sponsor($count)
    {
         $config = config::getInstance();
        $this->subject = $config->values['email-messages']['sponsor-subject'];
        $this->message = file_get_contents($config->values['email-messages']['sponsor-file']);
        $this->message = str_replace("%X%", $count, $this->message);
    }

    public function newsite()
    {
         $config = config::getInstance();
        $this->subject = $config->values['email-messages']['newsite-subject'];
        $this->message = file_get_contents($config->values['email-messages']['newsite-file']);
    }

    public function enroll($user)
    {
         $config = config::getInstance();
        $this->subject = $config->values['email-messages']['enrollment-subject'];
        $this->message = file_get_contents($config->values['email-messages']['enrollment-file']);
        $this->message = str_replace("%LOGIN%", $user->login, $this->message);
        $this->message = str_replace("%PASS%", $user->passsword, $this->message);
        $this->message = str_replace("%SPONSOR%", $user->sponsor, $this->message);
        $this->message = str_replace("%THUMBPRINT%", $config->values['radcert-thumbprint'],
            $this->message);
    }

    public function logrequest()
    {
         $config = config::getInstance();
        $subject = $config->values['email-messages']['logrequest-subject'];
        $message = file_get_contents($config->values['email-messages']['logrequest-file']);
    }

    public function send()
    {
         $config = config::getInstance();
        $provider = 1;
        $success = false;

        while ($success == false and isset($config->values['email-provider' . $provider]))
        {
            $success = emailprovider($provider);
            $provider++;
        }
    }

    function emailprovider($provider)
    {
        $config = config::getInstance();
        $conf_index = 'email-provider' . $provider;
        $key = $config[$conf_index]['key'];
        $data[$config->values[$conf_index]['text-field']] = $this->message;
        $data[$config->values[$conf_index]['from-field']] = $this->from->text;
        $data[$config->values[$conf_index]['to-field']] = $this->to->text;
        $data[$config->values[$conf_index]['subject-field']] = $this->subject;
        if ($this->filename != "")
        {
            $data[$config->values[$conf_index]['attachment-field']] = new CURLFile($this->
                filename);
        }
        $ch = curl_init($config->values[$conf_index]['url']);
        curl_setopt($ch, CURLOPT_USERPWD, $config->values[$conf_index]['user'] . ":" . $config->values[$conf_index]['key']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        // This is the result from the API
        curl_close($ch);
        if (preg_match($config->values[$conf_index]['success-regex'], $result))
            return true;
        else
            return false;
    }

}

?>
