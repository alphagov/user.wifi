<?php

class emailRequest
{
    public $emailFrom;
    public $emailTo;
    public $emailToCMD;
    public $emailBody;
    public $emailSubject;

    public function verify()
    {
        $user = new user;
        $user->identifier = $this->emailFrom;
        $user->verify($this->extractMobileNo());
    }
   
  
    public function logRequest()
    {


        $orgAdmin = new orgAdmin($this->emailFrom->text);
        if ($orgAdmin->authorised)
        {

            $report = new report;
            $report->orgAdmin = $orgAdmin;
            error_log("EMAIL: processing log request from : " . $this->emailFrom->text .
                " representing " . $orgAdmin->org_name);
            $subjectArray = explode(":", $this->emailSubject, 2);
            $reportType = strtolower(trim($subjectArray[0]));
            if (count($subjectArray) > 1)
            {
                $criteria = trim($subjectArray[1]);
            }
            switch ($reportType)
            {
                case "topsites":
                    $report->topSites();
                    error_log("Top Sites report generated records:" . count($report->result));
                    break;

                case "sitelist":
                    $report->siteList();
                    error_log("Site list generated records:" . count($report->result));
                    break;
                case "site":
                    $report->bySite($criteria);
                    error_log("Site list generated records:" . count($report->result));
                    break;

                case "user":
                    $report->byUser($criteria);
                    error_log("User report generated records:" . count($report->result));
                    break;

                default:
                    $report->byOrgId();
                    error_log("Report by Org ID generated records:" . count($report->result));
                    break;

            }


            // Create report pdf
            $pdf = new pdf;
            $pdf->populateLogRequest($orgAdmin);
            $pdf->landscape = true;
            $pdf->generatePDF($report);
            // Create email response and attach the pdf
            $email = new emailResponse;
            $email->to = $orgAdmin->email;
            $email->logrequest();
            $email->filename = $pdf->filename;
            $email->filepath = $pdf->filepath;
            $email->send();
            // Create sms response for the code
            $sms = new smsResponse;
            $sms->to = $orgAdmin->mobile;
            $sms->logrequest($pdf);

        }
    }

    public function newSite()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();

        $orgAdmin = new orgAdmin($this->emailFrom->text);
        if ($orgAdmin->authorised)
        {
            error_log("EMAIL: processing new site request from : " . $this->emailFrom->text);
            // Add the new site & IP addresses
            $site = new site();
            $site->loadByAddress($this->emailSubject);
            if (!$site->id) {
                $site->org_id = $orgAdmin->org_id;
                $site->org_name = $orgAdmin->org_name;
                $site->name = $this->emailSubject;
                error_log("EMAIL: creating new site : ".$site->name);
                $site->setRADKey();
                $site->updateFromEmail($this->emailBody);
                $site->writeRecord();
            }
            else if ($site->updateFromEmail($this->emailBody)) {
                error_log("EMAIL: updating site atributes : ".$site->name);
                $site->writeRecord();
            }

            $newSiteIPs = $this->ipList();
            if (count($newSiteIPs) >0) 
                error_log("EMAIL: Adding client IP addresses : ".$site->name);
            $site->addIPs($newSiteIPs);
            
            $newSiteSourceIPs = $this->sourceIpList();
            if (count($newSiteSiteIPs) >0) 
                error_log("EMAIL: Adding source IP addresses : ".$site->name);
            $site->addSourceIPs($newSiteSourceIPs);
            // Create the site information pdf
            $pdf = new pdf;
            $pdf->populateNewSite($site);
            $report = new report;
            $report->orgAdmin = $orgAdmin;
            $report->getIPList($site);
            $pdf->generatePDF($report);
            // Create email response and attach the pdf
            $email = new emailResponse;
            $email->to = $orgAdmin->email;
            if (count($newSiteIPs)>0) {
                $email->newSite();
            }
            else
            {
                $email->newSiteBlank();
            }
            $email->filename = $pdf->filename;
            $email->filepath = $pdf->filepath;
            $email->send();
            // Create sms response for the code
            $sms = new smsResponse;
            $sms->to = $orgAdmin->mobile;
            $sms->newsite($pdf);


        } else
        {
            error_log("EMAIL: Ignoring new site request from : " . $this->emailFrom->text);
        }
    }

    private function extractMobileNo()
    {
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $contact)
            {
                $contact = new identifier(trim($contact));
                if ($contact->validMobile)
                    {
                    return $contact;
                    }
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
        $list = array();

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

    private function sourceIpList()
    {
        $list = array();

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->emailBody) as $ipAddr)
        {
            $ipAddr = explode("-",trim($ipAddr));
            if (count($ipAddr) == 2 and filter_var($ipAddr[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) and filter_var($ipAddr[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            {
                $list[] = array("min" => $ipAddr[0],'max' => $ipAddr[1]);
            }
        }
        return $list;
    }


    public function fromAuthDomain()
    {
        $config = config::getInstance();
        return (preg_match($config->values['authorised-domains'], $this->emailFrom->
            text));
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