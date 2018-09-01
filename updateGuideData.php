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

    echo 'Fetching Program Data...' . PHP_EOL;
    $chunks = array_chunk($programIDs, 5000);
    $count = count($chunks);
    $programs = [];
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
    $MD5sAPI = $guide->fetchMD5s();
    file_put_contents('guide-data/md5s.json', json_encode($MD5sAPI));

    $recheckArray = [];

    echo "Checking MD5s..." . PHP_EOL;
    foreach ($MD5sFile as $stationID => $dates) {
        $datesToRecheck = [];

        foreach ($dates as $date => $data) {
            if (!isset($MD5sAPI->$stationID->$date)) { // date is in the past
                continue;
            }

            if ($data->md5 !== $MD5sAPI->$stationID->$date->md5) {
                echo "    $date MD5 doesn't match" . PHP_EOL;
                $datesToRecheck[] = $date;
            }
        }

        if (count($datesToRecheck) > 0) {
            $recheckArray[] = [
                'stationID' => $stationID,
                'dates' => $datesToRecheck
            ];
        }
    }

    echo 'Checking for new dates...' . PHP_EOL;
    foreach ($MD5sAPI as $stationID => $dates) {
        $datesToRecheck = [];

        foreach ($dates as $date => $data) {
            if (!isset($MD5sFile->$stationID->$date)) {
                echo "    Found new date $date" . PHP_EOL;
                $datesToRecheck[] = $date;
            }
        }

        if (count($datesToRecheck) > 0) {
            $recheckArray[] = [
                'stationID' => $stationID,
                'dates' => $datesToRecheck
            ];
        }
    }

    if (count($recheckArray) === 0) {
        echo 'No dates need updating. Exiting...';
        exit;
    }

    $listings = $guide->fetchListings(json_encode($recheckArray));

    echo 'Fetching Program IDs...' . PHP_EOL;
    $stations = [];
    $programIDs = [];
    foreach ($listings as $listing) {
        $programIDs = array_merge($programIDs, array_column($listing->programs, 'programID'));
    }

    echo 'Fetching Program Data...' . PHP_EOL;
    $chunks = array_chunk($programIDs, 5000);
    $count = count($chunks);
    $programs = [];
    foreach ($chunks as $index => $chunk) {
        echo "$index out of $count\r";
        $programs = array_merge($programs, $guide->fetchPrograms($chunk));
    }

    echo 'Reading sationID.json files...' . PHP_EOL;
    $stations = [];
    foreach (glob('guide-data/*.json') as $file) {
        $stationID = substr($file, 11, -5);
        $stations[$stationID] = json_decode(file_get_contents($file));
    }

    echo 'Updating station -> listing -> programs array...' . PHP_EOL;
    $count = count($listings);
    foreach ($listings as $index => $listing) {
        echo "$index out of $count\r";
        if (!isset($stations[$listing->stationID])) {
            $stations[$listing->stationID] = [];
        }

        $programIDs = array_column($listing->programs, 'programID');
        $date = $listing->metadata->startDate;
        $stations[$listing->stationID]->$date = array_filter($programs, function($program)  use ($programIDs) {
            return in_array($program->programID, $programIDs);
        });
    }

    echo 'Writing stationID.json files...' . PHP_EOL;
    foreach ($stations as $stationID => $dates) {
        foreach ($dates as $date => $data) {
            if (strtotime($date) < (time() - (7 * 24 * 60 * 60))) {
                unset($dates->$date);
            }
        }

        file_put_contents("guide-data/$stationID.json", json_encode($dates));
    }
}


?>
