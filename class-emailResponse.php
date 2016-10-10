<?php

class emailResponse
{
    public $from;
    public $to;
    public $subject;
    public $message;
    public $filename;
    public $filepath;

    public function __construct()
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
        $this->message = str_replace("%PASS%", $user->password, $this->message);
        $this->message = str_replace("%SPONSOR%", $user->sponsor->text, $this->message);
        $this->message = str_replace("%THUMBPRINT%", $config->values['radcert-thumbprint'],
            $this->message);
        $this->send();
    }

    public function logrequest()
    {
        $config = config::getInstance();
        $this->subject = $config->values['email-messages']['logrequest-subject'];
        $this->message = file_get_contents($config->values['email-messages']['logrequest-file']);
    }

    public function send()
    {
        $config = config::getInstance();
        $client = SesClient::factory(array(
	'region' => 'eu-west-1', 
        'key'    => $config->values['AWS']['Access-keyID'],
        'secret' => $config->values['AWS']['Access-key']
        ));
	$mailer = new SesRawMailer($client);
        $messageId = $mailer->send($this->to, 
				$this->subject,
				$this->message,
				$this->from,
				'<pre>'.$this->message.</pre>,
				array($this->filepath)

				 );
    }

    function tryEmailProvider($provider)
    {
        $config = config::getInstance();
        $conf_index = 'email-provider' . $provider;
        $success = false;
        if ($config->values[$conf_index]['enabled'])
        {
            switch ($config->values[$conf_index]['provider'])
            {
                case "postmark":
                    $success = $this->tryPostMark($provider);
                    break;
                case "mailgun":
                    $success = $this->tryMailGun($provider);
                    break;
            }

        }
        return $success;
    }

}

?>
