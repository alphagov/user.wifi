<?php

class smsResponse
{
    public $from;
    public $to;
    public $message;

    public function __constructor()
    {
        $this->set_noreply();

    }

    public function set_reply()
    {
        $config = config::getInstance();
        $this->from = $config->values['noreply-sender'];

    }
    public function set_noreply()
    {
        $config = config::getInstance();
        $this->from = $config->vlues['reply-sender'];
    }

    public function send()
    {
        $config = config::getInstance();
        // choose which provider to use to send the message
        // knock off the +
        $dest = str_replace("+", "", $dest);

        // remove any double spaces (when not using keywords)
        $message = str_replace("  ", " ", $message);
        $provider = 1;
        $success = false;
        while ($success == false and isset($config->values['sms-provider' . $provider]))
        {
            if ($this->providercanhandle($provider))
                $success = $this->tryProvider();
            $provider++;
        }

    }
    
    private function tryProvider($provider)
    {
        $config = config::getInstance();
        $conf_index = 'sms-provider' . $provider;
        $key = $config->values[$conf_index]['key'];
        $message = urlencode($message);
        $data = $config->values[$conf_index]['user-field'] . '=' . $config->values[$conf_index]['user'] .
            '&' . $config->values[$conf_index]['key-field'] . '=' . $config->values[$conf_index]['key'] .
            '&' . $config->values[$conf_index]['message-field'] . '=' . $message . '&' . $config->values[$conf_index]['from-field'] .
            '=' . $this->from . '&' . $config->values[$conf_index]['to-field'] . '=' . $this->to;
        $ch = curl_init($config->values[$conf_index]['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch); // This is the result from the API
        curl_close($ch);
        if (preg_match($config->values[$conf_index]['success-regex'], $result))
            return true;
        else
            return false;
    }


    public function terms()
    {
        $config = config::getInstance();
        $this->message = file_get_contents($config->values['sms-messages']['terms-file']);
        $this->message = str_replace("%KEYWORD%", $config->values['reply-keyword'], $this->
            message);
        $this->send();

    }

    public function security()
    {
        $config = config::getInstance();
        $this->message = file_get_contents($config->values['sms-messages']['security-file']);
        $this->message = str_replace("%THUMBPRINT%", $config->values['radcert-thumbprint'],
            $this->message);
        $this->send();

    }

    public function help($os)
    {
        $config = config::getInstance();

        switch ($os)
        {
            case (preg_match("/OSX/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['osx-help-file']);
                break;
            case (preg_match("/win.*(XP|7|8)/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['win-help-file']);
                break;
            case (preg_match("/win.*10/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['win10-help-file']);
                break;
            case (preg_match("/android/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['android-help-file']);
                break;
            case (preg_match("/(ios|ipad|iphone|ipod)/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['ios-help-file']);
                break;
            case (preg_match("/blackberry/i", $os) ? true : false):
                $this->message = file_get_contents($config->values['sms-messages']['blackberry-help-file']);
                break;
            default:
                $this->message = file_get_contents($config->values['sms-messages']['unknown-help-file']);
                break;
        }
        $this->send();
    }

    private function isinternational()
    {

        $config = config::getInstance();

        if (substr($this->to, 1, 2) == $config->values['country-code'])
            return false;
        else
            return true;
    }


    private function providercanhandle($provider)
    {
        $config = config::getInstance();
        if (!$this->isinternational() or $config->values['sms-provider' . $provider]['international'] ==
            1)
        {
            return true;
        } else
        {
            return false;
        }
    }


}

?>