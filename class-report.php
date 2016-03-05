<?php

class report
{
    public $orgAdmin;
    public $result;
    public $subject;
    public $columns;


    public function getIPList($site)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = "select nasname, shortname, secret from nas where org_id = ? and shortname = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $site->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site->name, PDO::PARAM_STR);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
        $this->subject = "List of IP Addresses configured for" . $site->name;
        $this->columns = array(
            "IP Address",
            "Site Name",
            "RADIUS Secret");


    }
    function siteList()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = "select distinct name, shortname from nas, organisation where organisation.id=nas.org_id order by name";
        $handle = $dblink->prepare($sql);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
        $this->subject = "List of sites subscribed to user.wifi";
        $this->columns = array("IP Address", "Site Name");
    }
    function topSites()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = 'select shortname, count(distinct username) as usercount from logs where authdate > DATE_SUB(NOW(), INTERVAL 30 DAY) and reply = "Access-Accept" group by shortname having usercount > 2 order by usercount desc';
        $handle = $dblink->prepare($sql);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
        $this->subject = "Sites by number of unique users in the last 30 days";
        $this->columns = array("Site Name", "Users");
    }
    function byOrgId()
    {

        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $sql = "select authdate,username,reply,shortname,contact,sponsor from logs where org_id = ?";
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $this->orgAdmin->org_id, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
        $this->subject = "All authentications for " . $this->orgAdmin->org_name .
            " sites";
        $this->columns = array(
            "Date/Time",
            "Username",
            "Success/Failure",
            "Site Name",
            "Identity",
            "Sponsor");
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
        $this->subject = "All authentications for " . $site;
        $this->columns = array(
            "Date/Time",
            "Username",
            "Success/Failure",
            "Identity",
            "Sponsor");
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
        $this->subject = "All authentications by the user " . $user;
        $this->columns = array(
            "Date/Time",
            "Username",
            "Success/Failure",
            "Identity",
            "Sponsor");

    }

    function statsUsersPerDay($orgAdmin, $site = null)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();

        if ($site)
            $sitesql = 'and shortname = ?';

        $sql = 'select count(distinct(username)) as Users, date(authdate) as Date  from logs where org_id = ' .
            $this->orgAdmin->org_id . ' ' . $sitesql .
            ' and reply = "Access-Accept" and authdate > DATE_SUB(NOW(), INTERVAL 30 DAY) group by Date order by Date desc';
        $handle = $dblink->prepare($sql);
        $handle->bindValue(1, $orgAdmin->org_id, PDO::PARAM_INT);
        $handle->bindValue(2, $site, PDO::PARAM_INT);
        $handle->execute();
        $this->result = $handle->fetchAll(\PDO::FETCH_NUM);
        $this->columns = array(
            "Date/Time",
            "Username",
            "Success/Failure",
            "Site Name",
            "Identity",
            "Sponsor");


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