<?php

class ListingsDatabase {
    private $db;

    function __construct() {
        $this->db = new PDO("sqlite:listings.db");
    }

    // DB Interface
    function exec($query) {
        $result = $this->db->exec($query);
        if ($result === FALSE) {
            print_r($this->db->errorInfo());
            exit;
        }
    }

    function select($query) {
        $result = $this->db->query($query);
        if ($result === FALSE) {
            print_r($this->db->errorInfo());
            exit;
        }

        return $result;
    }

    // inserts
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
                $airDateTime = new DateTime($program->airDateTime);
                $airDateTime->setTimezone(new DateTimeZone('America/Chicago'));
                $airDateTime = $airDateTime->format('H:i:s Y-m-d');

                $query .= "($stationID, '{$program->programID}', '{$airDateTime}', {$program->duration})," . PHP_EOL;
            }
        }

        self::stripTrailing($query);

        $this->exec($query);
    }

    function insertPrograms($programs) {
        $count = 1;
        $pQuery = "INSERT INTO programs (program_id, resource_id, title, description, show_type) VALUES" . PHP_EOL;
        $cQuery = "INSERT INTO casts    (program_id, name, role, billing)                        VALUES" . PHP_EOL;
        $rQuery = "INSERT INTO crews    (program_id, name, role, billing)                        VALUES" . PHP_EOL;

        foreach ($programs as $program) {
            $pc = [];
            $pr = [];
            $title = $program->titles[0]->title120;

            $description = '';
            if (isset($program->descriptions)) {
                if (isset($program->descriptions->description100)) {
                    foreach ($program->descriptions->description100 as $description100) {
                        if ($description100->descriptionLanguage === "en") {
                            $description = $description100->description;
                        }
                    }
                } else if (isset($program->descriptions->description1000)) {
                    foreach ($program->descriptions->description1000 as $description1000) {
                        if ($description1000->descriptionLanguage === "en") {
                            $description = $description1000->description;
                        }
                    }
                }
            }

            $resourceID = 'NULL';
            if (isset($program->resourceID)) {
                $resourceID = $program->resourceID;
            }

            $title       = str_replace("'", "''", $title);
            $description = str_replace("'", "''", $description);

            $pQuery .= "('{$program->programID}', $resourceID, '$title', '$description', '{$program->showType}')," . PHP_EOL;

            if (isset($program->cast)) {
                foreach ($program->cast as $person) {
                    $name = str_replace("'", "''", $person->name);
                    $role = str_replace("'", "''", $person->role);

                    if (isset($pc[$program->programID . $name . $role])) {
                        //print_r($program);
                        //print_r($pc[$program->programID . $name . $role]);
                        //print_r($person);
                        'Found duplicate cast member. Program ID: ' . $program->programID . PHP_EOL;
                        continue;
                    }

                    $cQuery .= "('{$program->programID}', '$name', '{$role}', {$person->billingOrder})," . PHP_EOL;

                    $pc[$program->programID . $name . $role] = $person;
                }
            }

            if (isset($program->crew)) {
                foreach ($program->crew as $person) {
                    $name = str_replace("'", "''", $person->name);
                    $role = str_replace("'", "''", $person->role);

                    if (isset($pr[$program->programID . $name . $role])) {
                        //print_r($program);
                        //print_r($pr[$program->programID . $name . $role]);
                        //print_r($person);
                        'Found duplicate cast member. Program ID: ' . $program->programID . PHP_EOL;
                        continue;

                    }

                    $rQuery .= "('{$program->programID}', '$name', '{$role}', {$person->billingOrder})," . PHP_EOL;

                    $pr[$program->programID . $name . $role] = $person;
                }
            }
        }

        self::stripTrailing($pQuery);
        self::stripTrailing($cQuery);
        self::stripTrailing($rQuery);

        $this->exec($pQuery);
        $this->exec($cQuery);
        $this->exec($rQuery);
    }

    // Fetchs
    function fetchMD5s() {
        return $this->select("SELECT * FROM md5s");
    }

    // Utils
    private static function stripTrailing(&$query) {
        $query = substr($query, 0, -2);
    }
}
