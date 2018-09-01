<?php
// guide-data/stationID.json
// {
//     "date": [
//         programs
//         ...
//     ]
//     ...
//}

require('Guide.php');

$config = json_decode(file_get_contents('guideConfig.json'));

$guide = new Guide(
    'https://json.schedulesdirect.org/20141201',
    $config->credentials->username,
    $config->credentials->password,
    $config->stationMap
);

$guide->setStations();

if (!is_dir('guide-data')) { // Feels like the first time
    mkdir('guide-data');

    echo 'Fetching MD5s...' . PHP_EOL;
    $md5s = $guide->fetchMD5s();
    file_put_contents('guide-data/md5s.json', json_encode($md5s));

    echo 'Fetching Listings...' . PHP_EOL;
    $listings = $guide->fetchListings();

    echo 'Fetching Program IDs...' . PHP_EOL;
    $stations = [];
    $programIDs = [];
    foreach ($listings as $listing) {
        $programIDs = array_merge($programIDs, array_column($listing->programs, 'programID'));
    }

    $chunks = array_chunk($programIDs, 5000);

    echo 'Fetching Program Data...' . PHP_EOL;
    $programs = [];
    $count = count($chunks);
    foreach ($chunks as $index => $chunk) {
        echo "$index out of $count\r";
        $programs = array_merge($programs, $guide->fetchPrograms($chunk));
    }

    echo 'Creating station -> listing -> programs array...' . PHP_EOL;
    $count = count($listings);
    foreach ($listings as $index => $listing) {
        echo "$index out of $count\r";
        if (!isset($stations[$listing->stationID])) {
            $stations[$listing->stationID] = [];
        }

        $programIDs = array_column($listing->programs, 'programID');

        $stations[$listing->stationID][$listing->metadata->startDate] = array_filter($programs, function($program)  use ($programIDs) {
            return in_array($program->programID, $programIDs);
        });
    }

    echo 'Writing stationID.json files...' . PHP_EOL;
    foreach ($stations as $stationID => $dates) {
        file_put_contents("guide-data/$stationID.json", json_encode($dates));
    }
} else {
    $MD5sFile = json_decode(file_get_contents('guide-data/md5s.json'));
    $MD5sApi = $guide->fetchMD5s();
    $recheckArray = [];

    foreach ($MD5sFile as $stationID => $dates) {
        echo "Checking MD5s for station ID $stationID" . PHP_EOL;
        $datesToRecheck = [];

        foreach ($dates as $date => $data) {
            if (!isset($MD5sApi->$stationID->$date)) { // date is in the past
                unset($dates->$date);
                continue;
            }

            if ($data->md5 !== $MD5sApi->$stationID->$date->md5) {
                echo "    $date MD5 doesn't match" . PHP_EOL;
                $datesToRecheck[] = $date;
            }
        }

        $recheckArray[] = [
            'stationID' => $stationID,
            'dates' => $datesToRecheck
        ];
    }

    foreach ($MD5sApi as $stationID => $dates) {
        $datesToRecheck = [];

        foreach ($dates as $date => $data) {
            if (!isset($MD5sFile->$stationID->$date)) {
                $datesToRecheck[] = $date;
            }
        }

        $recheckArray[] = [
            'stationID' => $stationID,
            'dates' => $datesToRecheck
        ];
    }

    print_r($recheckArray);
}


?>
