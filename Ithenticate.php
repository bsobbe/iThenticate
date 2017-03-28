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
        $this->setSid($this->login());
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

        $args = array(
            'username' => new Value($this->getUsername()),
            'password' => new Value($this->getPassword())
        );

        $response = $client->send(new Request('login', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        $sid = $response['val']['me']['struct']['sid']['me']['string'];
        if (isset($sid) && $sid != null) {
            return $sid;
        } else {
            return false;
        }
    }

    public function submitDocument($essay_title, $author_firstname, $author_lastname, $filename, $document_content, $folder_number)
    {
        $client = new Client($this->getUrl());

        $uploads_array = array(
            new Value(
                array(
                    'title'        => new Value($essay_title),
                    'author_first' => new Value($author_firstname),
                    'author_last'  => new Value($author_lastname),
                    'filename'     => new Value($filename),
                    'upload'       => new Value($document_content, 'base64'),
                ),
                'struct'
            ),
        );

        $args = array(
            'sid' => new Value($this->getSid()),
            'folder' => new Value($folder_number),
            'submit_to' => new Value(1),
            'uploads' => new Value($uploads_array, 'array'),
        );

        $response = $client->send(new Request('document.add', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        $essay_id = $response['val']['me']['struct']['uploaded']['me']['array'][0]['me']['struct']['id']['me']['int'];
        if (isset($essay_id) && $essay_id != null) {
            return $essay_id;
        } else {
            return false;
        }
    }
}