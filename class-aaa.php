<?php

class aaa
{
    public $user;
    public $mac;
    public $siteIP;
    public $lastSeen;

    public function __construct($request)
    {
        $path = preg_replace('~^/api/~', '', $request);
        $parts = explode('/', $path);
        for ($x = 0; $x < count($parts); $x++)
        {
            switch ($parts[$x])
            {
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
    public function authorize()
    {

    }

}

?>