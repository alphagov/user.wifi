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
                    $this->user->login = $parts[$x + 1];
                    $this->user->loadRecord();
                    break;
                case "mac":
                    $this->mac = $parts[$x + 1];
                    break;
                case "ap":
                    $this->ap = $parts[$x + 1];
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

        $this->session = new session($acct['Acct-Session-Id']['value']);


        switch ($acct['Acct-Status-Type']['value'])
        {


            case 1:
                // Acct Start - Store session in Memcache
                $this->session->login = $acct['User-Name']['value'];
                $this->session->startTime = $acct['User-Name']['value'];
                $this->session->login = $acct['User-Name']['value'];
                $this->session->login = $acct['User-Name']['value'];
                break;
            case 2:
                // Acct Stop - store record in DB

                break;
            case 3:
                // Acct Interim

                break;
        }

    }
    public function postAuth()
    {
        if ($this->result == "Accept")
        {
            // insert a new entry into session
            $db = DB::getInstance();
            $dblink = $db->getConnection();
            $handle = $dblink->prepare('insert into sessions (start,siteIP,username,mac,ap) values (now(),:siteIP,:username,:mac,:ap)');
            $handle->bindValue(':siteIP', $this->siteIP, PDO::PARAM_STR);
            $handle->bindValue(':username', $this->user->login, PDO::PARAM_STR);
            $handle->bindValue(':mac', $this->mac, PDO::PARAM_STR);
            $handle->bindValue(':ap', $this->ap, PDO::PARAM_STR);
            $handle->execute();
        }

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