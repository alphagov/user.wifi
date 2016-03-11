<?php

class aaa
{
    public $user;
    public $mac;
    public $siteIP;
    public $lastSeen;
    public $type;
    public $responseHeader;
    public $responseBody;
    public $reqeustJson;

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
                    $this->lastSeen = $parts[$x + 1];
                    break;
                case "site":
                    $this->siteIP = $parts[$x + 1];
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
        $acct = json_decode($this->requestJson,true);
        error_log($acct[Acct-Status-Type]);
    }
    public function postAuth()
    {

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