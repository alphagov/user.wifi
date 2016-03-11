<?php

class MC {
     private static $instance; //The single instance
     public $m;
     
      public static function getInstance()
    {
        if (!self::$instance)
        { // If no instance then make one
            self::$instance = new self();
        }
        return self::$instance;
    }
    // Constructor
    private function __construct()
    {
        $config = config::getInstance();
        try
        {
        $this->m = new Memcached();
        $this->m->addServer($config->values['memcache-server'], 11211);    

        }

        catch (PDOException $e)
        {
            error_log($e->getMessage());
        }
    }
}



?>