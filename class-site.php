<?php

class site
{
    public $radkey;
    public $name;
    public $org_id;
    public $org_name;


    public function loadByIp($ipAddr)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $handle = $dblink->prepare('select shortname, org_id from nas WHERE nasname = ?');
        $handle->bindValue(1, $ipAddr, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        $this->name = $row['shortname'];
        $this->org_id = $row['org_id'];
    }
    public function addIPs($iplist)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        foreach ($iplist as $ip_addr)
        {
            $handle = $dblink->prepare('insert into nas (nasname, shortname, secret, org_id) VALUES (?,?,?,?)');
            $handle->bindValue(1, $ip_addr, PDO::PARAM_STR);
            $handle->bindValue(2, $this->name, PDO::PARAM_STR);
            $handle->bindValue(3, $this->radkey, PDO::PARAM_STR);
            $handle->bindValue(4, $this->org_id, PDO::PARAM_INT);
            try
            {
                $handle->execute();
            }
            catch (PDOException $e)
            {
                // if it already exists the insert will fail, silently continue.
            }


        }

    }

    public function setRadkey()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $handle = $dblink->prepare('select secret from nas WHERE shortname=? and org_id=?');
        $handle->bindValue(1, $this->name, PDO::PARAM_STR);
        $handle->bindValue(2, $this->org_id, PDO::PARAM_INT);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row)
            $this->radkey = $row['secret'];
        else
            $this->generateRandomRadKey();
    }

    private function generateRandomRadKey()
    {
        $config = config::getInstance();
        $length = $config->values['radius-password']['length'];
        $pattern = $config->value['radius-password']['regex'];
        $pass = preg_replace($pattern, "", base64_encode($this->strongRandomBytes($length *
            4)));
        $this->radkey = substr($pass, 0, $length);
    }
    private function strongRandomBytes($length)
    {
        $strong = false; // Flag for whether a strong algorithm was used
        $bytes = openssl_random_pseudo_bytes($length, $strong);

        if (!$strong)
        {
            // System did not use a cryptographically strong algorithm
            throw new Exception('Strong algorithm not available for PRNG.');
        }

        return $bytes;
    }


}

?>
