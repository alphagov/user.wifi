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
        return $sessionRecord;


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
    public function writeToDb()
    {

    }

}

?>