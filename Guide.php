<?php

class Guide {
    protected $username;
    protected $password;
    protected $token;
    protected $stationMap;
    public $apiUrl;
    public $stations;

    function __construct($apiUrl, $username, $password, $stationMap = []) {
        if (php_sapi_name() !== 'cli') {
            session_start();
        }

        $this->apiUrl = $apiUrl;
        $this->username = $username;
        $this->password = sha1($password);
        $this->stationMap = $stationMap;

        if (isset($_SESSION['token'])) {
            $this->token = $_SESSION['token'];
        } else {
            $this->setToken();
        }

        $this->setStations();
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
            [ 'credentials' => TRUE, 'token' => FALSE ]
        )->token;
        $this->token = $token;
        $_SESSION['token'] = $token;
    }

    function setStations() {
        $stations = $this->apiRequest(
            '/lineups/USA-OTA-60614', 'GET',
            [ 'headers' => [ 'Accept-Encoding: deflate' ] ]
        )->stations;

        if ($this->stationMap) {
            $callsigns = array_keys((array) $this->stationMap);
            $stations = array_filter((array) $stations, function($station) use ($callsigns) {
                return in_array($station->callsign, $callsigns);
            });
        }

        $this->stations = [];
        foreach ($stations as $station) {
            $this->stations[$station->stationID] = $station;
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
            return [ "stationID" => $station->stationID ];
        }, $this->stations));

        return $this->apiRequest(
            '/schedules', 'POST',
            [ 'json' => $postJson ]
        );
    }

    function fetchListingsForDay($day) {
        return $this->fetchListings(json_encode(array_map(function($station) use ($day) {
            return [ "stationID" => $station->stationID, "date" => [ $day ] ];
        }, $this->stations)));
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

    function fetchImageUris($programIDs) {
        $postJson = json_encode(array_map(function($programID) {
            return substr($programID, 0, 10);
        }, $programIDs));

        return $this->apiRequest(
            '/metadata/programs', 'POST',
            [
                'json' => $postJson
            ]
        );
    }

    // Static HTML Render Functions
    public static function displayStationTitle($station) {
        print '<h2>' . $station->callsign;
        if (isset($station->affiliate)) {
            print '(' . $station->affiliate . ')';
        }

        print '</h2>';

    }

    public static function displayProgram($station, $program) { ?>
    <div class="program-box">
        <h4 class="title"><?php echo $program->titles[0]->title120 ?></h4>
        <?php if (isset($program->imageUri)) { ?>
            <img src="<?php echo $program->imageUri ?>" />
        <?php } ?>

        <?php if (isset($program->descriptions->description100)) { ?>
            <p class="description"><?php echo $program->descriptions->description100[0]->description ?></p>
        <?php } else if (isset($program->descriptions->description1000)) { ?>
            <p class="description"><?php echo $program->descriptions->description1000[0]->description ?></p>
        <?php } ?>

        <?php if (isset($program->cast)) { ?>
            <ul class="cast">
                <?php foreach ($program->cast as $cast) { ?>
                    <?php if (intval($cast->billingOrder) > 5) continue; ?>
                    <li><?php echo $cast->name ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <?php if (isset($program->movie) && isset($program->movie->qualityRating)) { ?>
            <ul class="ratings">
                <?php foreach ($program->movie->qualityRating as $rating) { ?>
                    <li><?php echo "{$rating->rating} out of {$rating->maxRating} ({$rating->ratingsBody} min:{$rating->minRating} inc:{$rating->increment})" ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <span class="button record-button"
                onclick="recordProgram(<?php echo "'{$program->airDateTime}', '{$station->callsign}', {$program->duration}, '{$program->titles[0]->title120}'" ?>)"
        >Record</span>
    </div>
    <?php }

    public static function printStyles() { ?>
        <style>
         .program-box {
             border: saddlebrown 2px solid;
             clear: both;
             margin: auto;
             margin-bottom: 20px;
             max-width: 60%;
             overflow: auto;
             padding: 10px 10px 10px 10px;
         }

         .program-box .title {

         }

         .program-box .description {
             float: right;
             text-align: left;
             padding-left: 10px;
             width: 75%;
         }

         .program-box ul { /* .cast .ratings */
             float: right;
             list-style-type: none;
             text-align: left;
             width: 75%;
         }

         .program-box img {
             float: left;
             width: 25%;
         }

         .program-box .record-button {
             clear: both;
             float: right;
         }

        </style>
    <?php }

    public static function printJS() { ?>
        <script type="text/javascript">
         function recordProgram(airTime, callsign, duration, title) {
             jQuery.ajax({
                 url: '/record.php',
                 'data': { airTime, callsign, duration, title },
                 success: function(response) {
                     console.log(response);
                 }
             });
         }
        </script>
    <?php }
}


?>
