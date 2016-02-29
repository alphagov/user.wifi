<?php


class org_admin
{
    public $email;
    public $org_name;
    public $org_id;
    public $mobile;
    public $name;
    public $authorised;

    public function __constructor($email)
    {
        global $dblink, $configuration;
        $this->email = $email;
        $handle = $dblink->prepare('select id,mobile,orgname,name from orgs_admins_view where email=?');
        $handle->bindValue(1, $contact, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            // if a row is returned then that user is an authorised contact
            $this->org_id = $row['id'];
            $this->org_name = $row['orgname'];
            $this->name = $row['name'];
            $this->mobile = $row['mobile'];
            $this->authorised = true;
        } else {
            $this->authorised = false;
        }
    }
}

?>
