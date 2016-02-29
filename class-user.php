<?php

class user
{
    public $identifier;
    public $login;
    public $password;
    public $sponsor;

    public function loaduser($login)
    {
        $this->login = $login;
        $this->set_identifier();
    }

    public function enroll($force = false)
    {
        $this->set_username();
        $this->set_wifi_password();
        if ($force)
            $this->newpassword();
        $this->radius_db_write();
        $this->send_credentials();
    }


    private function send_credentials()
    {
        if ($this->identifier->valid_mobile)
        {
            $sms = new sms_response();
            $sms->set_reply();
            $sms->to = $this->identifier->text;
            $sms->enroll($this);
            $sms->send();
        } else
            if ($this->identifier->valid_email)
            {
                $email = new email_response();
                $email->to = $this->identifier->text;
                $email->enroll($this);
                $email->send();

            }

    }

    private function radius_db_write()
    {
        global $dblink;
        // Delete any old accounts for that username
        $lc_wifilogin = strtolower($this->login);
        $handle = $dblink->prepare('delete from radcheck where username=?');
        $handle->bindValue(1, $this->login, PDO::PARAM_STR);
        $handle->execute();
        // Insert the new account details
        $handle = $dblink->prepare('insert into radcheck (username, attribute, op, value) VALUES (:login,"Cleartext-Password",":=",:pass)');
        $handle->bindValue(':login', $this->login, PDO::PARAM_STR);
        $handle->bindValue(':pass', $this->password, PDO::PARAM_STR);
        $handle->execute();
        // Insert mapping from username to email/phone
        $handle = $dblink->prepare('insert into userdetails (username, contact, sponsor) VALUES
                                    (:login,:contact,:sponsor) ON DUPLICATE KEY UPDATE sponsor=:sponsor');
        $handle->bindValue(':login', $this->login, PDO::PARAM_STR);
        $handle->bindValue(':contact', $this->identifier->text, PDO::PARAM_STR);
        $handle->bindValue(':sponsor', $this->sponsor->text, PDO::PARAM_STR);
        $handle->execute();
    }

    public function newpassword()
    {
        # This will force the generation of a new password for the user
        $this->set_wifi_password(true);
    }

    private function set_wifi_password($force = false)
    {
        # This function looks for an existing password entry for this username
        # if it finds it and force is false then it will return the same password
        # otherwise it will return a randomly generated one
        global $dblink;
        $row = false;
        if (!$force)
        {
            $handle = $dblink->prepare('select distinct value from radcheck where attribute = "Cleartext-Password" and username=?');
            $handle->bindValue(1, $this->login, PDO::PARAM_STR);
            $handle->execute();
            $row = $handle->fetch(\PDO::FETCH_ASSOC);
        }
        if ($row)
        {
            $this->password = $row['value'];
        } else
        {
            $this->password = generate_random_wifi_password();
        }
    }

    private function username_is_unique($uname)
    {
        global $dblink;
        $handle = $dblink->prepare('select count(username) as unamecount from userdetails where username=?');
        $handle->bindValue(1, $uname, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row['unamecount'] == 0)
            return true;
        else
            return false;
    }

    private function set_username()
    {
        global $dblink;
        $handle = $dblink->prepare('select distinct username from userdetails where contact=?');
        $handle->bindValue(1, $this->identifier->text, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row)
        {
            $username = $row['username'];
        } else
        {
            $username = $this->generate_random_username();

            while (!$this->username_is_unique($username))
            {
                $username = $this->generate_random_username();
            }
        }
        $this->login = $username;
    }

    private function get_identifier()
    {
        global $dblink;
        $handle = $dblink->prepare('select distinct contact from userdetails where username=?');
        $handle->bindValue(1, $this->login, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row)
        {
            $this->identifier = new identifier($row['contact']);
        }

    }
}

?>