<?php

namespace bsobbe\ithenticate;

class Ithenticate
{
    private $username;
    private $password;
    private $sid;

    public function __construct($username, $password)
    {

    }

    public function setUsername($username)
    {
        if (property_exists($this, 'username')) {
            $this->username = $username;
        }
    }

    public function setPassword($password)
    {
        if (property_exists($this, 'password')) {
            $this->password = $password;
        }
    }

    public function setSid($sid)
    {
        if (property_exists($this, "sid")) {
            $this->sid = $sid;
        }
    }

    public function getUsername()
    {
        if (property_exists($this, 'username')) {
            return $this->username;
        }
    }

    public function getPassword()
    {
        if (property_exists($this, 'password')) {
            return $this->password;
        }
    }

    public function getSid()
    {
        if (property_exists($this, 'sid')) {
            return $this->sid;
        }
    }
}