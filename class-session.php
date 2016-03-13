<?php

class session
{
    
    public $id;
    public $inOctets;
    public $outOctets;
    public $login;
    public $startTime;
    public $stopTime;

    public function __construct($id)
    {
        $this->id = $id;
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


    public function sessionRecord()
    {
        $sessionRecord['login'] = $this->login;
        $sessionRecord['InO'] = $this->inOctets;
        $sessionRecord['OutO'] = $this->outOctets;
        $sessionRecord['Start'] = $this->startTime;
        return $sessionRecord;


    }
    public function addInOctets($inO){
    $this->inOctets += $inO;    
    }
    
    public function addOutOctets($outO) {
        
    }
    
    public function writeToCache()
    {
        $m = MC::getInstance();
        $sessionRecord = $m->m->set($this->id, $this->getSessionRecord());

    }
    public function writeToDb()
    {

    }

}

?>