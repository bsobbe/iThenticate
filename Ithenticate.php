<?php

namespace bsobbe\ithenticate;

use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;

class Ithenticate
{
    private $url;
    private $username;
    private $password;
    private $sid;

    public function __construct($username, $password)
    {
        $this->setUrl("https://api.ithenticate.com/rpc");
        $this->setUsername($username);
        $this->setPassword($password);
        $this->login();
    }

    public function setUrl($url)
    {
        if (property_exists($this, 'url')) {
            $this->url = $url;
        }
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

    public function getUrl()
    {
        if (property_exists($this, 'url')) {
            return $this->url;
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

    private function login()
    {
        $client = new Client($this->getUrl());
        $value = new value;

        $args = array(
            'username' => new Value($this->getUsername()),
            'password' => new Value($this->getPassword())
        );

        $response = $client->send(new Request('login', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        $sid = $response['val']['me']['struct']['sid']['me']['string'];
        //return $sid;
        var_dump($sid);
    }
}