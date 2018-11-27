<?php

require('Guide.php');
$config = json_decode(file_get_contents('guideConfig.json'));
$guide = new Guide(
    'https://json.schedulesdirect.org/20141201',
    $config->credentials->username,
    $config->credentials->password,
    $config->stationMap
);

require("ListingsDatabase.php");
$db = new ListingsDatabase();

echo "Fetching Schedules Direct MD5s..." . PHP_EOL;
$md5sApi = $guide->fetchMD5s();

echo "Fetching Local MD5s..." . PHP_EOL;
$md5sdb = $db->fetchMD5s();

$md5sKeyed = array();
foreach ($md5sdb as $md5) {
    $md5sKeyed[$md5['station_id'] . $md5['date']] = $md5['md5'];
}

$deletes = array();
$updates = array();
foreach ($md5sApi as $stationID => $_date) {
    foreach ($_date as $date => $md5) {
        if (!array_key_exists($stationID . $date, $md5sKeyed)) {
            $deletes[] = array($stationID, $date);
        } else if ($md5sKeyed[$stationID . $date] !== $md5->md5) {
            $updates[] = array($stationID, $date);
        }

    }
}

print_r($deletes);
print_r($updates);
