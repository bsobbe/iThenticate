<?php

namespace bsobbe\ithenticate;

/*
 * Using needed classes from PhpXmlRpc library.
 */
use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;

class Ithenticate
{
    /*
     * This property is for ithenticate API Url.
     */
    private $url;
    /*
     * These properties are for storing Ithenticate's API Username and Password.
     */
    private $username;
    private $password;
    //This property will be filled with the login session hash value returned by Ithenticate's API after logging in.
    private $sid;

    /*
     * Construct method initializes Url, Username, and Password in properties.
     * It also logs in using login method and puts the login hash session into
     * the defined property.
     */
    public function __construct($username, $password)
    {
        $this->setUrl("https://api.ithenticate.com/rpc");
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setSid($this->login());
    }

    /*
     * This is setter method for Ithenticate's API Url.
     */
    public function setUrl($url)
    {
        if (property_exists($this, 'url')) {
            $this->url = $url;
        }
    }

    /*
     * This is setter method for Ithenticate's API Username.
     */
    public function setUsername($username)
    {
        if (property_exists($this, 'username')) {
            $this->username = $username;
        }
    }

    /*
     * This is setter method for Ithenticate's API Password.
     */
    public function setPassword($password)
    {
        if (property_exists($this, 'password')) {
            $this->password = $password;
        }
    }

    /*
     * This is setter method for Ithenticate's responded hash login session.
     */
    public function setSid($sid)
    {
        if (property_exists($this, "sid")) {
            $this->sid = $sid;
        }
    }

    /*
     * This is getter method for Ithenticate's API Url.
     */
    public function getUrl()
    {
        if (property_exists($this, 'url')) {
            return $this->url;
        }
    }

    /*
     * This is getter method for Ithenticate's API Username.
     */
    public function getUsername()
    {
        if (property_exists($this, 'username')) {
            return $this->username;
        }
    }

    /*
     * This is getter method for Ithenticate's API Password.
     */
    public function getPassword()
    {
        if (property_exists($this, 'password')) {
            return $this->password;
        }
    }

    /*
     * This is getter method for Ithenticate's responded hash login session.
     */
    public function getSid()
    {
        if (property_exists($this, 'sid')) {
            return $this->sid;
        }
    }

    /*
     * This method logs into Ithenticate using Username and Password
     * The return value is Ithenticate's login hash session which will
     * be used in other methods as the authentication.
     */
    private function login()
    {
        $client = new Client($this->getUrl());
        $args = array(
            'username' => new Value($this->getUsername()),
            'password' => new Value($this->getPassword())
        );

        $response = $client->send(new Request('login', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['sid']['me']['string'])) {
            $sid = $response['val']['me']['struct']['sid']['me']['string'];
            if ($sid != null) {
                return $sid;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * This method submits new documents into Ithenticate into your prefered folder.
     * The return value is the unique number of the submitted document which will be
     * used for getting result and other actions which will be performed on document.
     * The last parameter in the method is the number of the folder which you want to
     * save the document in it.
     */
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
        if (isset($response['val']['me']['struct']['uploaded']['me']['array'][0]['me']['struct']['id']['me']['int'])) {
            $document_id = $response['val']['me']['struct']['uploaded']['me']['array'][0]['me']['struct']['id']['me']['int'];
            if ($document_id != null) {
                return $document_id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function fetchDocumentReportState($document_id)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'id' => new Value($document_id),
        );

        $response = $client->send(new Request('document.get', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['documents']['me']['array'][0]['me']['struct']['is_pending']['me']['int'])) {
            $state = $response['val']['me']['struct']['documents']['me']['array'][0]['me']['struct']['is_pending']['me']['int'];
            if ($state !== null) {
                $is_pending['is_pending'] = $state;
                return $is_pending;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function fetchDocumentReportId($document_id)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'id' => new Value($document_id),
        );

        $response = $client->send(new Request('document.get', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['documents']['me']['array'][0]['me']['struct']['parts']['me']['array'][0]['me']['struct']['id']['me']['int'])) {
            $report_id = $response['val']['me']['struct']['documents']['me']['array'][0]['me']['struct']['parts']['me']['array'][0]['me']['struct']['id']['me']['int'];
            if ($report_id != null) {
                return $report_id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function fetchDocumentReportUrl($report_id, $exclude_biblio = 1, $exclude_quotes = 1, $exclude_small_matches = 1)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'id' => new Value($report_id),
            'exclude_biblio' => new Value($exclude_biblio),
            'exclude_quotes' => new Value($exclude_quotes),
            'exclude_small_matches' => new Value($exclude_small_matches),
        );

        $response = $client->send(new Request('report.get', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['view_only_url']['me']['string'])) {
            $report_url = $response['val']['me']['struct']['view_only_url']['me']['string'];
            if ($report_url != null) {
                return $report_url;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}