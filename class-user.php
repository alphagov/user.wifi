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
        $this->setIdentifier();
    }

    public function enroll($force = false)
    {
        $this->setUsername();
        $this->setWifiPassword();
        if ($force)
            $this->newPassword();
        $this->radiusDbWrite();
        $this->sendCredentials();
    }


    private function sendCredentials()
    {
        if ($this->identifier->validMobile)
        {
            $sms = new sms_response();
            $sms->setReply();
            $sms->to = $this->identifier->text;
            $sms->enroll($this);
            $sms->send();
        } else
            if ($this->identifier->validEmail)
            {
                $email = new emailResponse();
                $email->to = $this->identifier->text;
                $email->enroll($this);
                $email->send();

            }

    }

    private function radiusDbWrite()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        // Delete any old accounts for that username
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

    public function newPassword()
    {
        # This will force the generation of a new password for the user
        $this->setWifiPassword(true);
    }

    private function setWifiPassword($force = false)
    {
        # This function looks for an existing password entry for this username
        # if it finds it and force is false then it will return the same password
        # otherwise it will return a randomly generated one
        $db = DB::getInstance();
        $dblink = $db->getConnection();
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
            $this->password = generateRandomWifiPassword();
        }
    }

    private function UsernameIsUnique($uname)
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $handle = $dblink->prepare('select count(username) as unamecount from userdetails where username=?');
        $handle->bindValue(1, $uname, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row['unamecount'] == 0)
            return true;
        else
            return false;
    }

    private function setUsername()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
        $handle = $dblink->prepare('select distinct username from userdetails where contact=?');
        $handle->bindValue(1, $this->identifier->text, PDO::PARAM_STR);
        $handle->execute();
        $row = $handle->fetch(\PDO::FETCH_ASSOC);
        if ($row)
        {
            $username = $row['username'];
        } else
        {
            $username = $this->generateRandomUsername();

            while (!$this->usernameIsUnique($username))
            {
                $username = $this->generateRandomUsername();
            }
        }
        $this->login = $username;
    }

    private function getIdentifier()
    {
        $db = DB::getInstance();
        $dblink = $db->getConnection();
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