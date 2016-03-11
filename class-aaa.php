<?php

class aaa
{
    public $user;
    public $password;
    public $mac;
    public $siteIP;
    public $memcache;
    
    public function __construct($request)
    {
        $path = preg_replace('~^/api/~', '', $request);
        $parts = explode('/', $path);
        for ($x = 0; $x < count($parts); $x++)
        {
            switch ($parts[$x])
            {
                case "user":
                    $this->user = $parts[$x + 1];
                    break;
                case "mac":
                    $this->mac = $parts[$x + 1];
                    break;
                case "site":
                    $this->siteIP = $parts[$x + 1];
                    break;
            }

        }

    }
    public function authenticate() {
        
    }
    public function loadFromCache() {
        $m = MC::getInstance();
$this->password = $m->m->get("PW".$this->user);

if (!($ip = $m->get('ip_block'))) {
    if ($m->getResultCode() == Memcached::RES_NOTFOUND) {
        $ip = array();
        $m->set('ip_block', $ip);
    }
    public function saveToCache() {
        
    }
    public function getPassword() {
        $this->loadFromCache();
        
    }
}

?>