<?php

class Guide {
    protected $apiUrl;
    protected $username;
    protected $password;
    protected $token;
    protected $stationMap;
    protected $stations;

    function __construct($apiUrl, $username, $password, $stationMap = []) {
        session_start();

        $this->apiUrl = $apiUrl;
        $this->username = $username;
        $this->password = sha1($password);
        $this->stationMap = $stationMap;

        if (isset($_SESSION['token'])) {
            $this->token = $_SESSION['token'];
        } else {
            $this->setToken();
        }
    }

    function apiRequest($url, $type = 'GET', $options = []) {
        $defaults = [
            'credentials' => FALSE,
            'token' => TRUE,
            'debug' => FALSE,
            'headers' => FALSE,
            'json' => FALSE,
        ];
        $options += $defaults;

        $curl = curl_init($this->apiUrl . $url);

        $headers = [];

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_USERAGENT, 'tv-serve https://github.com/mstone121/tv-serve');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);

        if ($options['debug']) {
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        }

        if ($options['headers']) {
            $headers = array_merge($headers, $options['headers']);
        }

        if ($this->token) {
            $headers[] = 'token: ' . $this->token;
        }

        if ($type === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);

            $postFields = [];
            if ($options['credentials']) {
                $postFields['username'] = $this->username;
                $postFields['password'] = $this->password;
            }

            if ($postFields) {
                $json = json_encode($postFields);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($json);
            }

            if ($options['json']) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $options['json']);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($options['json']);
            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if (($error = curl_error($curl))) {
            return $error;
        }

        if ($options['debug']) {
            echo curl_getinfo($curl, CURLINFO_HEADER_OUT);
        }

        return json_decode($response);

    }

    function setToken() {
        $token = $this->apiRequest(
            '/token', 'POST',
            array(
                'credentials' => TRUE,
                'token' => FALSE
            )
        )->token;
        $this->token = $token;
        $_SESSION['token'] = $token;
    }

    function setStations() {
        $apiStations = $this->apiRequest(
            '/lineups/USA-OTA-60614', 'GET',
            [ 'headers' => [ 'Accept-Encoding: deflate' ] ]
        )->stations;

        if ($this->stationMap) {
            $callsigns = array_keys((array) $this->stationMap);
            $this->stations = array_filter((array) $apiStations, function($station) use ($callsigns) {
                return in_array($station->callsign, $callsigns);
            });
        } else {
            $this->stations = (array) $apiStations;
        }
    }

    function fetchMD5s() {
        $postJson = json_encode(array_map(function($station) {
            return [ "stationID" => $station->stationID ];
        }, $this->stations));

        return $this->apiRequest(
            '/schedules/md5', 'POST',
            [ 'json' => $postJson ]
        );
    }

    function fetchListings($postJson = FALSE) {
        $postJson = $postJson ?: json_encode(array_map(function($station) {
            return array( "stationID" => $station->stationID );
        }, $this->stations));

        return $this->apiRequest(
            '/schedules', 'POST',
            [ 'json' => $postJson ]
        );
    }

    function fetchPrograms($programIDs) {
        $postJson = json_encode($programIDs);

        return $this->apiRequest(
            '/programs', 'POST',
            [
                'json' => $postJson,
                'headers' => [
                    'Accept-Encoding: deflate'
                ]
            ]
        );
    }
}


?>
