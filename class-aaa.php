<?php

class aaa
{
    public $user;
    public $mac;
    public $siteIP;
    public $ap;
    public $type;
    public $responseHeader;
    public $responseBody;
    public $reqeustJson;
    public $session;
    public $result;
    
    public function __construct($request)
    {
        $parts = explode('/', $request);
        for ($x = 0; $x < count($parts); $x++)
        {
            switch ($parts[$x])
            {
                case "api":
                    $this->type = $parts[$x + 1];
                    break;
                case "user":
                    $this->user = new user;
                    $this->user->login = strtoupper($parts[$x + 1]);
                    $this->user->loadRecord();
                    break;
                case "mac":
                    $this->setMac($parts[$x + 1]);
                    break;
                case "ap":
                    $this->ap = setAp($parts[$x + 1]);
                    break;
                case "site":
                    $this->siteIP = $parts[$x + 1];
                    break;
                case "result":
                    $this->result = $parts[$x + 1];
                    break;
            }

        }


    }
    public function processRequest()
    {
        switch ($this->type)
        {
            case "authorize":
                $this->authorize();
                break;
            case "post-auth":
                $this->postAuth();
                break;
            case "accounting":
                $this->accounting();
                break;

        }

    }
    public function accounting()
    {
        $acct = json_decode($this->requestJson, true);

        $this->session = new session($this->user.$acct['Acct-Session-Id']['value'][0]);


        switch ($acct['Acct-Status-Type']['value'][0])
        {


            case 1:
                // Acct Start - Store session in Memcache
                $this->session->login = $acct['User-Name']['value'][0];
                $this->session->startTime = time();
                $this->setMac($acct['Calling-Station-Id']['value'][0]);
                $this->setAp($acct['Called-Station-Id']['value'][0]);
                $this->session->mac = $this->mac;
                $this->session->ap = $this->ap;
                $this->session->siteIP = $this->siteIP;
                $this->session->writeToCache();
                error_log("Accounting start: " . $this->session->login . " " . $this->session->
                    id);
                    $this->responseHeader = "HTTP/1.0 204 OK";
                break;
            case 2:
                // Acct Stop - store record in DB - if there is no start record do nothing.
                if ($this->session->startTime)
                {
                    $this->session->inOctets += $acct['Acct-Input-Octets']['value'][0];
                    $this->session->outOctets += $acct['Acct-Output-Octets']['value'][0];
                    $this->session->stopTime = time();
                    $this->session->deleteFromCache();
                    error_log("Accounting stop: " . $this->session->login . " " . $this->session->
                        id);

                    error_log("Accounting stop: " . $this->session->login . " " . $this->session->
                        id . " InMB: " . $this->session->inMB() . " OutMB: " . $this->session->outMB() .
                        "site: " . $this->session->siteIP . " mac: " . $this->session->mac . " ap: " . $this->
                        session->ap);
                    $this->session->writeToDB();
                    $this->responseHeader = "HTTP/1.0 204 OK";
                }
                break;
            case 3:
                // Acct Interim - if there is no start record do nothing.
                if ($this->session->startTime)
                {
                    $this->session->inOctets += $acct['Acct-Input-Octets']['value'][0];
                    $this->session->outOctets += $acct['Acct-Output-Octets']['value'][0];
                    $this->session->writeToCache();
                    error_log("Accounting update: " . $this->session->login . " " . $this->session->
                        id);
                        $this->responseHeader = "HTTP/1.0 204 OK";
                }
                break;
        }

    }


    public function postAuth()
    {
        if ($this->result == "Access-Accept")
        {
            if ($this->user->login != "HEALTH")
            {
                // insert a new entry into session (unless it's a health check)
                $db = DB::getInstance();
                $dblink = $db->getConnection();
                $handle = $dblink->prepare('insert into sessions (start,siteIP,username,mac,ap) values (now(),:siteIP,:username,:mac,:ap)');
                $handle->bindValue(':siteIP', $this->siteIP, PDO::PARAM_STR);
                $handle->bindValue(':username', $this->user->login, PDO::PARAM_STR);
                $handle->bindValue(':mac', strtoupper($this->mac), PDO::PARAM_STR);
                $handle->bindValue(':ap', strtoupper($this->ap), PDO::PARAM_STR);
                $handle->execute();

                // Code to do per site actions is here

            }

        }

    }
    
    private function fixMac($mac) {
        //convert to upper case
        $mac = strtoupper($mac);
        //get rid of anything that isn't hex
        $mac = preg_replace('/[^0-F]/'," ",$mac);
        // recreate the mac in IETF format using the first 12 chars. 
        $mac = substr($mac,0,2).'-'.substr($mac,2,2).'-'.substr($mac,4,2).'-'.substr($mac,6,2).'-'.substr($mac,8,2).'-'.substr($mac,10,2);
        return $mac;       
    }
    
    public function setMac($mac) {
        $this->mac=$this->fixMac($mac);
    }
    public function setAp($mac) {
        $this->ap=$this->fixMac($mac);
    }
    public function authorize()
    {
        if ($this->user->identifier->text)
        {
            $this->responseHeader = "HTTP/1.0 200 OK";
            $response['control:Cleartext-Password'] = $this->user->password;
            $this->responseBody = json_encode($response);
        } else
        {
            $this->responseHeader = "HTTP/1.0 401 Forbidden";

        }

    }
}

?>