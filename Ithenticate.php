<?php

namespace bsobbe\ithenticate;

/**
 * Using needed classes from PhpXmlRpc library.
 */
use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;

class Ithenticate
{
    /**
     * This property is for ithenticate API Url.
     */
    private $url;
    /**
     * These properties are for storing Ithenticate's API Username and Password.
     */
    private $username;
    private $password;
    //This property will be filled with the login session hash value returned by Ithenticate's API after logging in.
    private $sid;

    /**
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

    /**
     * This is setter method for Ithenticate's API Url.
     */
    public function setUrl($url)
    {
        if (property_exists($this, 'url')) {
            $this->url = $url;
        }
    }

    /**
     * This is setter method for Ithenticate's API Username.
     */
    public function setUsername($username)
    {
        if (property_exists($this, 'username')) {
            $this->username = $username;
        }
    }

    /**
     * This is setter method for Ithenticate's API Password.
     */
    public function setPassword($password)
    {
        if (property_exists($this, 'password')) {
            $this->password = $password;
        }
    }

    /**
     * This is setter method for Ithenticate's responded hash login session.
     */
    public function setSid($sid)
    {
        if (property_exists($this, "sid")) {
            $this->sid = $sid;
        }
    }

    /**
     * This is getter method for Ithenticate's API Url.
     */
    public function getUrl()
    {
        if (property_exists($this, 'url')) {
            return $this->url;
        }
    }

    /**
     * This is getter method for Ithenticate's API Username.
     */
    public function getUsername()
    {
        if (property_exists($this, 'username')) {
            return $this->username;
        }
    }

    /**
     * This is getter method for Ithenticate's API Password.
     */
    public function getPassword()
    {
        if (property_exists($this, 'password')) {
            return $this->password;
        }
    }

    /**
     * This is getter method for Ithenticate's responded hash login session.
     */
    public function getSid()
    {
        if (property_exists($this, 'sid')) {
            return $this->sid;
        }
    }

    /**
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

        if ($response['val']['me']['struct']['status']['me']['int'] === 401) {
            throw new \Exception($response['val']['me']['struct']['messages']['me']['array'][0]['me']['string'], 401);
        }

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

    /**
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

    /**
     * Create a new group
     */
    public function createGroup($group_name)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'name' => new Value($group_name),
        );

        $response = $client->send(new Request('group.add', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['id']['me']['int'])) {
            return $response['val']['me']['struct']['id']['me']['int'];
        }
        return false;
    }

    /**
     * Create a new folder
     */
    public function createFolder($folder_name, $folder_description, $group_id, $exclude_quotes, $add_to_index)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'folder_group' => new Value($group_id),
            'name' => new Value($folder_name),
            'description' => new Value($folder_description),
            'exclude_quotes' => new Value($exclude_quotes),
            'add_to_index' => new Value($add_to_index),
        );

        $response = $client->send(new Request('folder.add', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['id']['me']['int'])) {
            return $response['val']['me']['struct']['id']['me']['int'];
        }
        return false;
    }


    /**
     * This method fetch all subfolders in a specific folder
     */
    public function fetchFolderInGroup($folderId)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'id' => new Value($folderId),
            'r' => new Value('500'),
        );
        $response = $client->send(new Request('group.folders', array(new Value($args, "struct"))));

        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['folders']['me']['array'])) {
            return array_combine(
                array_map(function($o) {
                    return $o['me']['struct']['id']['me']['int'];
                }, $response['val']['me']['struct']['folders']['me']['array']),
                array_map(function($o) {
                    return $o['me']['struct']['name']['me']['string'];
                }, $response['val']['me']['struct']['folders']['me']['array'])
            );
        } else {
            return false;
        }
    }

    /**
     * Retrieve the group list
     */
    public function fetchGroupList()
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
        );

        $response = $client->send(new Request('group.list', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['groups']['me']['array'])) {
            return array_combine(
                array_map(function($o) {
                    return $o['me']['struct']['id']['me']['int'];
                }, $response['val']['me']['struct']['groups']['me']['array']),
                array_map(function($o) {
                    return $o['me']['struct']['name']['me']['string'];
                }, $response['val']['me']['struct']['groups']['me']['array'])
            );
        } else {
            return false;
        }
    }

    /**
     * Retrieve the folder list
     */
    public function fetchFolderList()
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
        );

        $response = $client->send(new Request('folder.list', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);
        if (isset($response['val']['me']['struct']['folders']['me']['array'])) {
            return array_combine(
                array_map(function($o) {
                    return $o['me']['struct']['id']['me']['int'];
                }, $response['val']['me']['struct']['folders']['me']['array']),
                array_map(function($o) {
                    return $o['me']['struct']['name']['me']['string'];
                }, $response['val']['me']['struct']['folders']['me']['array'])
            );
        } else {
            return false;
        }
    }

    /**
     * Launches the report.get request.
     *
     * @param int $document_id
     *   The document ID.
     *
     * @return array
     *   An array containing the following keys: sid, api_status,
     *   response_timestamp, id, pager, documents, folder
     */
    public function documentGetRequest(int $document_id)
    {
        $client = new Client($this->getUrl());
        $args = array(
            'sid' => new Value($this->getSid()),
            'id' => new Value($document_id),
        );

        $response = $client->send(new Request('document.get', array(new Value($args, "struct"))));
        $response = json_decode(json_encode($response), true);

        $return = [];
        if (isset($response['val']['me']['struct']['documents']['me']['array'])) {
            foreach ($response['val']['me']['struct']['documents']['me']['array'] as $index => $document_array) {
                $document_data = $document_array['me']['struct'];
                if (isset($document_data['author_first']['me']['string'])) {
                    $return['documents'][$index]['author_first'] = $document_data['author_first']['me']['string'];
                }
                if (isset($document_data['author_last']['me']['string'])) {
                    $return['documents'][$index]['author_last'] = $document_data['author_last']['me']['string'];
                }
                $return['documents'][$index]['is_pending'] = $document_data['is_pending']['me']['int'];
                $return['documents'][$index]['id'] = $document_data['id']['me']['int'];
                $return['documents'][$index]['processed_time'] = $document_data['processed_time']['me']['dateTime.iso8601'];
                $return['documents'][$index]['percent_match'] = $document_data['percent_match']['me']['int'];
                $return['documents'][$index]['title'] = $document_data['title']['me']['string'];
                $return['documents'][$index]['uploaded_time'] = $document_data['uploaded_time']['me']['dateTime.iso8601'];

                if (isset($document_data['parts']['me']['array'])) {
                    foreach ($document_data['parts']['me']['array'] as $part_index => $parts_array) {
                        $part_data = $parts_array['me']['struct'];
                        $return['documents'][$index]['parts'][$part_index]['max_percent_match'] = $part_data['max_percent_match']['me']['int'];
                        $return['documents'][$index]['parts'][$part_index]['processed_time'] = $part_data['processed_time']['me']['string'];
                        $return['documents'][$index]['parts'][$part_index]['score'] = $part_data['score']['me']['int'];
                        $return['documents'][$index]['parts'][$part_index]['words'] = $part_data['words']['me']['int'];
                        $return['documents'][$index]['parts'][$part_index]['id'] = $part_data['id']['me']['int'];
                        $return['documents'][$index]['parts'][$part_index]['doc_id'] = $part_data['doc_id']['me']['int'];
                    }
                }
            }
            $return['sid'] = $response['val']['me']['struct']['sid']['me']['string'];
            $return['api_status'] = $response['val']['me']['struct']['api_status']['me']['int'];
            $return['response_timestamp'] = $response['val']['me']['struct']['response_timestamp']['me']['dateTime.iso8601'];
            $return['folder'] = $this->readFolder($response);
        } else {
            $return['errors'] = $this->readErrors($response);
        }

        return $return;
    }

    /**
     * Launches the report.get request.
     *
     * @param int $report_id
     *   The document report ID.
     * @param int $exclude_biblio
     *   (optional) Whether to exclude biblio references. Defaults to 1.
     * @param int $exclude_quotes
     *   (optional) Whether to exclude quoted text. Defaults to 1.
     * @param int $exclude_small_matches
     *   (optional) Whether to exclude small matches. Defaults to 1.
     *
     * @return array
     *   An array containing the following keys: sid, api_status,
     *   response_timestamp, report_url, view_only_url, view_only_expires.
     */
    public function reportGetRequest(int $report_id, $exclude_biblio = 1, $exclude_quotes = 1, $exclude_small_matches = 1)
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

        $return = [];
        if (isset($response['val']['me']['struct']['view_only_url']['me']['string'])) {
            $return['sid'] = $response['val']['me']['struct']['sid']['me']['string'];
            $return['api_status'] = $response['val']['me']['struct']['api_status']['me']['int'];
            $return['response_timestamp'] = $response['val']['me']['struct']['response_timestamp']['me']['dateTime.iso8601'];

            // The request is successful and all data exist.
            $return['view_only_url'] = $response['val']['me']['struct']['view_only_url']['me']['string'];
            $return['view_only_expires'] = $response['val']['me']['struct']['view_only_expires']['me']['dateTime.iso8601'];
            $return['report_url'] = $response['val']['me']['struct']['report_url']['me']['string'];
        } else {
            $return['errors'] = $this->readErrors($response);
        }

        return $return;
    }

    /**
     * Fetch the document report state
     */
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

    /**
     * Fetch the document report ID
     */
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

    /**
     * Fetch the document report URL
     */
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

    /**
     * Converts the errors in the repsponse into an easy to handle array.
     *
     * @param array $response_array
     *   The response after it has been envoded and decoded into an array.
     *
     * @return array
     *   A multidimensioonal array where the first level is the property related
     *   to the error and the value is an array of error messages.
     */
    protected function readErrors($response_array) {
        if (!isset($response_array['val']['me']['struct']['errors']['me']['struct'])) {
            return [];
        }

        $return = [];
        foreach ($response_array['val']['me']['struct']['errors']['me']['struct'] as $element_name => $error_array) {
            if (!isset($error_array['me']['array'])) {
                // Avoid breaking the application for possible future changes.
                continue;
            }
            foreach ($error_array['me']['array'] as $index => $error_data) {
                if (!isset($error_data['me']['string'])) {
                    // Avoid breaking the application if there is an error of a
                    // different format.
                    continue;
                }
                $return[$element_name][$index] = $error_data['me']['string'];
            }
        }
        return $return;
    }

    /**
     * Converts the folders entry in the repsponse into an easy to handle array.
     *
     * @param array $response_array
     *   The response after it has been envoded and decoded into an array.
     *
     * @return array
     *   A multidimensioonal array that contains information related to a
     *   folder.
     */
    protected function readFolder($response_array) {
        if (!isset($response_array['val']['me']['struct']['folder']['me']['struct'])) {
            return [];
        }

        $return = [];
        $folder_array = $response_array['val']['me']['struct']['folder']['me']['struct'];
        $return['minimum_match_word_count'] = $folder_array['minimum_match_word_count']['me']['int'];
        $return['exclude_word_count'] = $folder_array['exclude_word_count']['me']['int'];
        $return['exclude_percent'] = $folder_array['exclude_percent']['me']['int'];
        $return['exclude_abstracts'] = $folder_array['exclude_abstracts']['me']['int'];
        $return['exclude_by_percent'] = $folder_array['exclude_by_percent']['me']['int'];
        $return['id'] = $folder_array['id']['me']['int'];
        $return['group'] = $folder_array['group']['me']['struct']['id']['me']['int'];
        $return['exclude_biblio'] = $folder_array['exclude_biblio']['me']['int'];
        // Ignore the 'collection_detail' as it is a complex array by itself.
        // Ignore the 'collections' as it is a complex array by itself.
        $return['exclude_quotes'] = $folder_array['exclude_quotes']['me']['int'];
        $return['limit_match_size'] = $folder_array['limit_match_size']['me']['int'];
        $return['exclude_methods'] = $folder_array['exclude_methods']['me']['int'];
        $return['name'] = $folder_array['name']['me']['string'];
        $return['setup_time'] = $folder_array['setup_time']['me']['dateTime.iso8601'];
        $return['exclude_small_matches'] = $folder_array['exclude_small_matches']['me']['int'];

        return $return;
    }
}
