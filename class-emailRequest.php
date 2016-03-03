<?php

class emailRequest
{
    public $emailFrom;
    public $emailTo;
    public $emailToCMD;
    public $emailBody;
    public $emailSubject;

    

    public function enroll()
    {
        // Self enrollment request
        if ($this->fromAuthDomain())
        {
            error_log("EMAIL: Self Enrolling : ".$this->emailFrom->text);
            $user = new user;
            $user->identifier = $this->emailFrom;
            $user->sponsor = $this->emailFrom;
            $user->enroll();
        
        } else
        {
            error_log("EMAIL: Ignoring self enrollment from : ".$this->emailFrom->text);
        }
    }

    public function sponsor()
    {
        if ($this->fromAuthDomain())
        {
            error_log("EMAIL: Sponsored request from: ".$this->emailFrom->text);
            $enrollcount = 0;

            foreach ($this->contactList() as $identifier)
            {
                $enrollcount++;
                $user = new user;
                $user->identifier = $identifier;
                $user->sponsor = $this->emailFrom;
                $user->enroll();
            }
            $email = new emailResponse();
            $email->to = $this->emailFrom->text;
            $email->sponsor($enrollcount);
            $email->send();
        } else
        {
            error_log("EMAIL: Ignoring sponsored reqeust from : ".$this->emailFrom->text);
        }
    }

    public function newSite()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        
        $orgAdmin = new orgAdmin($this->emailFrom->text);
        if ($orgAdmin->authorised)
        {
            error_log("EMAIL: processing new site request from : ".$this->emailFrom->text);
            // Add the new site & IP addresses
            $site = new site();
            $site->org_id = $orgAdmin->org_id;
            $site->org_name = $orgAdmin->org_name;
            $site->name = $this->emailSubject;
            $site->setRADKey();
            $site->addIPs($this->ipList());
            // Create the site information pdf
            $pdf = new pdf;
            $pdf->populateNewSite($site);
            
            $pdf->generatePDF($site->getIPList());
            // Create email response and attach the pdf
            $email = new emailResponse;
            $email->to = $orgAdmin->email;
            $email->newSite();
            $email->filename = $pdf->filename;
            $email->send();
            // Create sms response for the code
            $sms = new smsResponse;
            $sms->to=$orgAdmin->mobile;
            


        } else
        {
            error_log("EMAIL: Ignoring new site request from : ". $this->emailFrom->text);
        }
    }

  
    private function contactList()
    {

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $contact)
        {
            $contact = new identifier(trim($contact));
            if ($contact->validEmail or $contact->validMobile)
            {
                $list[] = $contact;
            }
        }
        return $list;
    }

    private function ipList()
    {

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $ipAddr)
        {
            $ipAddr = trim($ipAddr);
            if (filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            {
                $list[] = $ipAddr;
            }
        }
        return $list;
    }

    public function fromAuthDomain()
    {
        $config = config::getInstance();
        return (preg_match($config->values['authorised-domains'], $this->emailFrom->text));
    }

    public function setEmailSubject($subject)
    {
        $this->emailSubject = $subject;
    }
    public function setEmailBody($body)
    {
        $this->emailBody = strip_tags(strtolower($body));
    }

    public function setEmailTo($to)
    {
        $this->emailTo = $to;
        $this->emailToCMD = strtolower(trim(strtok($this->emailTo, "@")));
    }

    public function setEmailFrom($from)
    {
        $this->emailFrom = new identifier($from);
    }
}

?>