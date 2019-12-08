<?php

if (!is_file('listings.db')) {
    echo "Expecting database table at listings.db. Exiting...";
    exit;
}

echo "Setting up guide interface..." . PHP_EOL;
require("Guide.php");
$guide = new Guide(json_decode(file_get_contents('guideConfig.json')));

echo "Setting up database interface..." . PHP_EOL;
require("ListingsDatabase.php");
$db = new ListingsDatabase();

echo "Fetching MD5s..." . PHP_EOL;
$md5s = $guide->fetchMD5s();

echo "Inserting MD5s..." . PHP_EOL;
$db->insertMD5s($md5s);

echo 'Fetching Listings...' . PHP_EOL;
$listings = $guide->fetchListings();

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
