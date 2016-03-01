<?php

class smsResponse
{
    public $from;
    public $to;
    public $message;

    public function __construct()
    {
        $this->setNoReply();
    }

    public function setReply()
    {
        $config = config::getInstance();
        $this->from = $config->values['noreply-sender'];

    }
    public function setNoReply()
    {
        $config = config::getInstance();
        $this->from = $config->values['reply-sender'];
    }

    public function send()
    {
        $config = config::getInstance();
        // choose which provider to use to send the message
        // knock off the +
        $this->to = str_replace("+", "", $this->to);

        // remove any double spaces (when not using keywords)
        $this->message = str_replace("  ", " ", $this->message);
        $provider = 1;
        $success = false;
        while ($success == false and isset($config->values['sms-provider' . $provider]))
        {
            if ($this->providerCanHandle($provider))
                $success = $this->tryProvider($provider);
            $provider++;
        }

    }

    private function tryProvider($provider)
    {
        print "Sending " . $this->message . " to " . $this->to . "<BR>";
        $config = config::getInstance();
        $confIndex = 'sms-provider' . $provider;
        $key = $config->values[$confIndex]['key'];
        $data = $config->values[$confIndex]['user-field'] . '=' . $config->values[$confIndex]['user'] .
            '&' . $config->values[$confIndex]['key-field'] . '=' . $config->values[$confIndex]['key'] .
            '&' . $config->values[$confIndex]['message-field'] . '=' . urlencode($this->
            message) . '&' . $config->values[$confIndex]['from-field'] . '=' . $this->from .
            '&' . $config->values[$confIndex]['to-field'] . '=' . $this->to;
        print "<PRE>" . $data . "</PRE>";
        $ch = curl_init($config->values[$confIndex]['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch); // This is the result from the API
        curl_close($ch);
        print "<PRE>*****$result*****</PRE>";
        if (preg_match($config->values[$confIndex]['success-regex'], $result))
            return true;
        else
            return false;
    }
    public function enroll($user)
    {
        $config = config::getInstance();
        $message = file_get_contents($config->values['sms-messages']['enrollment-file']);
        $message = str_replace("%LOGIN%", $user->login, $message);
        $message = str_replace("%PASS%", $user->password, $message);
        $message = str_replace("%KEYWORD%", $config->values['reply-keyword'], $message);

        $this->message = $message;
        $this->send();
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

    private function isInternational()
    {

        $config = config::getInstance();

        if (substr($this->to, 1, 2) == $config->values['country-code'])
            return false;
        else
            return true;
    }


    private function providerCanHandle($provider)
    {
        $config = config::getInstance();
        if (!$this->isInternational() or $config->values['sms-provider' . $provider]['international'] ==
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