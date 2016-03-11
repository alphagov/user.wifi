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

    public function __construct($request)
    {
        $path = preg_replace('~^/api/~', '', $request);
        $parts = explode('/', $path);
        for ($x = 0; $x < count($parts); $x++)
        {
            switch ($parts[$x])
            {
                case "api":
                    $this->type = $parts[$x + 1];
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

        switch ($this->type)
        {
            case "authorize":
                $this->authorize();
                break;
            case "post-auth":
                $this->postAuth();
                break;
        }

    }
    public function postAuth()
    {

    }
    public function authorize()
    {
        if ($this->user->password)
        {
            $this->responseHeader = "HTTP/1.0 200 OK";
            $response['control:Cleartext-Password'] = $aaa->user->password;
            $this->responseBody = json_encode($response);
        } else
        {
            $this->responseHeader = "HTTP/1.0 401 Forbidden";

        }

    }

?>