<?php

class email_request
{
    public $emailFrom;
    public $emailTo;
    public $emailToCMD;
    public $emailBody;
    public $emailSubject;

    public function __construct()
    {
        set_emailFrom();
        set_emailTo();
        set_emailSubject();
        set_emailBody();
    }

    public function enroll()
    {
        // Self enrollment request
        if ($this->fromauthdomain())
        {
            error_log("EMAIL: Self Enrolling : $this->emailFrom");
            $user = new user;
            $user->identifier = $this->emailFrom;
            $user->enroll();
        } else
        {
            error_log("EMAIL: Ignoring self enrollment from : $emailFrom");
        }
    }

    public function sponsor()
    {
        if ($this->fromauthdomain())
        {
            error_log("EMAIL: Sponsored request from: $this->emailFrom");
            $enrollcount = 0;

            foreach ($this->contactlist() as $identifier)
            {
                $enrollcount++;
                $user = new user;
                $user->identifier = new identifier($identifier);
                $user->sponsor = $this->emailFrom;
                $user->enroll();
            }
            $email = new email_response();
            $email->to = $this->emailFrom;
            $email->sponsor($enrollcount);
            $email->send();
        } else
        {
            error_log("EMAIL: Ignoring sponsored reqeust from : $emailFrom");
        }
    }

    public function newsite()
    {
        global $dblink;
        $org_admin = new org_admin($this->emailFrom);
        if ($org_admin->authorised)
        {
            error_log("EMAIL: processing new site request from : $emailFrom");
            // Add the new site & IP addresses
            $site = new site();
            $site->org_id = $org_admin->org_id;
            $site->name = $this->emailSubject;
            $site->set_radkey();
            $site->add_ips($this->iplist());
            // Create the site information pdf
            $pdf = new pdf;
            $pdf->populate_newsite($site);
            $pdf->generatepdf($site->get_ip_list());
            // Create email response and attach the pdf
            $email = new email_response;
            $email->to = $this->emailFrom;
            $email->newsite();
            $email->filename = $pdf->filename;
            $email->send();
            // Create sms response for the code
            $sms = new sms_response;
            $sms->to=$org_admin->mobile;
            $sms->


        } else
        {
            error_log("EMAIL: Ignoring new site request from : $emailFrom");
        }
    }

  
    private function contactlist()
    {

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $contact)
        {
            $contact = trim($contact);
            if (filter_var($contact, FILTER_VALIDATE_EMAIL) or isvalidmobilenumber($contact))
            {
                $list[] = $ip_addr;
            }
        }
        return $list;
    }

    private function iplist()
    {

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $ip_addr)
        {
            $ip_addr = trim($ip_addr);
            if (filter_var($ip_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            {
                $list[] = $ip_addr;
            }
        }
        return $list;
    }

    public function fromauthdomain()
    {
        global $configuration;
        return (preg_match($configuration['authorised-domains'], $this->emailFrom));
    }

    private function set_emailSubject()
    {
        $this->emailSubject = $_REQUEST['subject'];
    }
    private function set_emailBody()
    {
        $this->emailBody = strip_tags(strtolower($_REQUEST['body-plain']));
    }

    private function set_emailTo()
    {
        $this->emailTo = $_REQUEST['recipient'];
        $this->emailToCMD = strtolower(trim(strtok(emailTo, "@")));
    }

    private function set_emailFrom()
    {
        $this->emailFrom = new identifier($_REQUEST['sender']);
    }
}

?>