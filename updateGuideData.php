<?php

require('Guide.php');
$guide = new Guide(json_decode(file_get_contents('guideConfig.json')));

require("ListingsDatabase.php");
$db = new ListingsDatabase();

echo "Fetching Schedules Direct MD5s..." . PHP_EOL;
$apiMD5s = $guide->fetchMD5s();
$apiDict = [];
foreach ($apiMD5s as $stationID => $dates) {
    foreach ($dates as $date => $md5) {
        $apiDict[$stationID . $date] = array(
            'station_id' => $stationID,
            'date' => $date,
            'md5' => $md5->md5
        );
    }
}

echo "Fetching Local MD5s..." . PHP_EOL;
$dbMD5s = $db->fetchMD5s();
$dbDict = [];
foreach ($dbMD5s as $md5) {
    $dbDict[$md5['station_id'] . $md5['date']] = $md5;
}

echo "Comparing MD5s..." . PHP_EOL;
$deletes = array();
$updates = array();

// md5s db doesn't have or has and are inaccurate
foreach ($apiDict as $md5) {
    if (!array_key_exists($md5['station_id'] . $md5['date'], $dbDict) ||
        $md5['md5'] !== $dbDict[$md5['station_id'] . $md5['date']]['md5']
    ) {
        if (!isset($updates[$stationID])) {
            $updates[$stationID] = [];
        }
        $updates[$stationID][$date] = new stdClass();
        $updates[$stationID][$date]->md5 = $md5['md5'];

        $deletes[] = $md5;
    }
}

foreach ($dbDict as $md5) {
    if (!array_key_exists($md5['station_id'] . $md5['date'], $apiDict)) {
        $deletes[] = $md5;
    }
}

if ($deletes) {
    echo "Deleting old Listings..." . PHP_EOL;
    $db->deleteMD5s($deletes);
    $db->deleteListings($deletes);
    $db->deleteOrphanedPrograms();
}

if ($updates) {
    echo 'Fetching Listings...' . PHP_EOL;
    $db->insertMD5s($updates);

    $listingsToFetch = [];
    foreach ($updates as $stationID => $dates) {
        $listingsToFetch[] = [
            "stationID" => $stationID,
            "date" => array_keys($dates)
        ];
    }

    $listings = $guide->fetchListings(json_encode($listingsToFetch));

    echo "Inserting Listings..." . PHP_EOL;
    $db->insertListings($listings);

    echo "Fetching Programs..." . PHP_EOL;
    $programIDs = [];
    foreach ($listings as $listing) {
        $programIDs = array_merge($programIDs, array_column($listing->programs, 'programID'));
    }
    $programIDs = array_unique($programIDs);

    $chunks = array_chunk($programIDs, 5000);
    $count = count($chunks);
    $programs = [];
    foreach ($chunks as $index => $chunk) {
        echo ($index + 1) . " out of $count\r";
        $programs = array_merge($programs, $guide->fetchPrograms($chunk));
    }

    echo "Inserting Programs..." . PHP_EOL;
    $db->insertPrograms($programs);
}
