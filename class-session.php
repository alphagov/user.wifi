<?php

class session
{

    public $id;
    public $inOctets;
    public $outOctets;
    public $login;
    public $startTime;
    public $stopTime;
    public $mac;
    public $ap;
    public $siteIP;

    public function __construct($id)
    {
        $this->id = $id;
        $this->loadFromCache();

    }


    public function sessionRecord()
    {
        $sessionRecord['login'] = $this->login;
        $sessionRecord['InO'] = $this->inOctets;
        $sessionRecord['OutO'] = $this->outOctets;
        $sessionRecord['Start'] = $this->startTime;
        $sessionRecord['siteIP'] = $this->siteIP;
        $sessionRecord['mac'] = $this->mac;
        $sessionRecord['ap'] = $this->ap;
        return $sessionRecord;


    }
    public function inMB()
    {

        return round($this->inOctets / 1000000);
    }
    public function outMB()
    {


        return round($this->outOctets / 1000000);
    }
    public function loadFromCache()
    {
        $m = MC::getInstance();
        $sessionRecord = $m->m->get($this->id);
        if ($sessionRecord)
        {
            $this->login = $sessionRecord['login'];
            $this->inOctets = $sessionRecord['InO'];
            $this->outOctets = $sessionRecord['OutO'];
            $this->startTime = $sessionRecord['Start'];
            $this->siteIP = $sessionRecord['siteIP'];
            $this->mac = $sessionRecord['mac'];
            $this->ap = $sessionRecord['ap'];
        }
    }
    public function deleteFromCache()
    {
        $m = MC::getInstance();
        $m->m->delete($this->id);
    }

    public function writeToCache()
    {
        $m = MC::getInstance();
        $m->m->set($this->id, $this->SessionRecord());

    }
    public function writeToDB()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $handle = $dblink->prepare('update sessions set stop=now(),inMB=:inMB, outMB=:outMB where siteIP=:siteIP and username=:username and stop is null mac=:mac, and ap=:ap)');
        $handle->bindValue(':siteIP', $this->siteIP, PDO::PARAM_STR);
        $handle->bindValue(':username', $this->login, PDO::PARAM_STR);
        $handle->bindValue(':mac', $this->mac, PDO::PARAM_STR);
        $handle->bindValue(':ap', $this->ap, PDO::PARAM_STR);
        $handle->bindValue(':inMB', $this->inMB(), PDO::PARAM_INT);
        $handle->bindValue(':outMB', $this->outMB(), PDO::PARAM_INT);
        $handle->execute();
    }

}

?>