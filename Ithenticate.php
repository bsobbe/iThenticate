<?php

namespace bsobbe\ithenticate;

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

    private function curlCall($request)
    {

        $url = $this->getUrl();
        $header[] = "Content-type: text/xml";
        $header[] = "Content-length: ".strlen($request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        } else {
            curl_close($ch);
            return $data;
        }
    }

    private function login()
    {
        if (isset($this->username) && isset($this->password)) {
            $args = array (
                "username" => xmlrpc_encode($this->getUsername()),
                "password" => xmlrpc_encode($this->getPassword()),
            );
            var_dump($this->curlCall($args));
        }
    }
}