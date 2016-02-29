<?php

class sms_response
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
        global $configuration;
        $this->from = $configuration['noreply-sender'];

    }
    public function set_noreply()
    {
        global $configuration;
        $this->from = $configuration['reply-sender'];
    }

    public function send()
    {
        global $configuration;
        // choose which provider to use to send the message
        // knock off the +
        $dest = substr($dest, 1);

        // remove any double spaces (when not using keywords)
        $message = str_replace("  ", " ", $message);
        $provider = 1;
        $success = false;
        while ($success == false and isset($configuration['sms-provider' . $provider]))
        {
            if ($this->providercanhandle($provider))
                $success = smsprovider($provider, $source, $dest, $message);
            $provider++;
        }

    }

    public function terms()
    {
        global $configuration;
        $this->message = file_get_contents($configuration['sms-messages']['terms-file']);
        $this->message = str_replace("%KEYWORD%", $configuration['reply-keyword'], $this->
            message);
        $this->send();

    }

    public function security()
    {
        global $configuration;
        $this->message = file_get_contents($configuration['sms-messages']['security-file']);
        $this->message = str_replace("%THUMBPRINT%", $configuration['radcert-thumbprint'],
            $this->message);
        $this->send();

    }

    public function help($os)
    {
        global $configuration;

        switch ($os)
        {
            case (preg_match("/OSX/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['osx-help-file']);
                break;
            case (preg_match("/win.*(XP|7|8)/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['win-help-file']);
                break;
            case (preg_match("/win.*10/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['win10-help-file']);
                break;
            case (preg_match("/android/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['android-help-file']);
                break;
            case (preg_match("/(ios|ipad|iphone|ipod)/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['ios-help-file']);
                break;
            case (preg_match("/blackberry/i", $os) ? true : false):
                $this->message = file_get_contents($configuration['sms-messages']['blackberry-help-file']);
                break;
            default:
                $this->message = file_get_contents($configuration['sms-messages']['unknown-help-file']);
                break;
        }
        $this->send();
    }

    private function isinternational()
    {

        global $configuration;
        if (substr($this->to, 1, 2) == $configuration['country-code'])
            return false;
        else
            return true;
    }


    private function providercanhandle($provider)
    {
        global $configuration;
        if (!$this->isinternational() or $configuration['sms-provider' . $provider]['international'] ==
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