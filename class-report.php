<?php

class report
{
    public $orgAdmin;
    public $result;

    function byOrgId()
    {

        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = "select authdate,username,reply,shortname,contact,sponsor from logs where org_id = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $this->orgAdmin->org_id, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
    }

    function bySite($site)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = "select authdate,username,reply,contact,sponsor from logs where org_id = ? and shortname = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $this->orgAdmin->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
    }

    function byUser($user)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();

        $sql = "select authdate,username,reply,contact,sponsor from logs where username = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $this->orgAdmin->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);

    }

    function statsUsersPerDay($orgAdmin, $site, $days)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();

        $sql = 'select count(distinct(username))  from logs where authdate >= "2016-03-02" and authdate < "2016-03-03" and reply = "Access-Accept"';
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $orgAdmin->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);


    }

    function userIdentifier($orgAdmin, $user)
    {
        $dblink = $db->getConnection();
        $sql = "select authdate,username,reply,contact,sponsor from logs where username = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $orgAdmin->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);

    }

}

?>