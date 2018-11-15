<?php

class ListingsDatabase {
    private $db;

    function __construct() {
        $this->db = new PDO("sqlite:listings.db");
    }

    function exec($query) {
        $result = $this->db->exec($query);
        if ($result === FALSE) {
            print_r($this->db->errorInfo());
            exit;
        }
    }

    function insertMD5s($md5s) {
        $query = "INSERT INTO md5s (station_id, date, md5) VALUES" . PHP_EOL;

        foreach ($md5s as $stationID => $dates) {
            foreach ($dates as $date => $data) {
                $query .= "($stationID, '$date', '{$data->md5}')," . PHP_EOL;
            }
        }

        self::stripTrailing($query);

        $this->exec($query);
    }

    function insertListings($listings) {
        $query = "INSERT INTO listings (station_id, program_id, air_time, duration) VALUES" . PHP_EOL;

        foreach ($listings as $listing) {
            $stationID = $listing->stationID;
            foreach ($listing->programs as $program) {
                $query .= "($stationID, '{$program->programID}', '{$program->airDateTime}', {$program->duration})," . PHP_EOL;
            }
        }

        self::stripTrailing($query);

        $this->exec($query);
    }

    // Utils
    private static function stripTrailing(&$query) {
        $query = substr($query, 0, -2);
    }
}
