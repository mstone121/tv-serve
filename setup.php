<?php

if (!is_file('listings.db')) {
    echo "Expecting database table at listings.db. Exiting...";
    exit;
}

echo "Setting up guide interface..." . PHP_EOL;
require("Guide.php");
$config = json_decode(file_get_contents("guideConfig.json"));
$guide = new Guide(
    "https://json.schedulesdirect.org/20141201",
    $config->credentials->username,
    $config->credentials->password,
    $config->stationMap
);
$guide->setStations();

echo "Setting up database interface..." . PHP_EOL;
require("ListingsDatabase.php");
$db = new ListingsDatabase();

echo "Fetching MD5s..." . PHP_EOL;
//$md5s = $guide->fetchMD5s();

echo "Inserting MD5s..." . PHP_EOL;
//$db->insertMD5s($md5s);

echo 'Fetching Listings...' . PHP_EOL;
$listings = $guide->fetchListings();

echo "Inserting Listings..." . PHP_EOL;
$db->insertListings($listings);
