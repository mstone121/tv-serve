<?php
require_once('Guide.php');

$config = json_decode(file_get_contents('../guideConfig.json'));

$guide = new Guide(
    'https://json.schedulesdirect.org/20141201',
    $config->credentials->username,
    $config->credentials->password,
    $config->stationMap
);

$guide->setStations();

if (!isset($_GET['date'])) {
    $date = date('Y-m-d');
} else {
    $date = $_GET['date'];
}

$listings = $guide->fetchListingsForDay($date);
$programIDs = [];

foreach ($listings as $station) {
    $programIDs = array_merge($programIDs, array_column($station->programs, 'programID'));
}

$programs = $guide->fetchPrograms($programIDs);
$programMap = [];

foreach ($programs as $program) {
    if ($program->showType === 'Feature Film') {
        $programMap[$program->programID] = $program;
    }
}

$imageUris = $guide->fetchImageUris(array_keys($programMap));
foreach ($imageUris as $images) {
    foreach ($images->data as $image) {
        if ($image->category === 'Box Art') {
            foreach ($programMap as $programID => $program) {
                if (strpos($programID, $images->programID) === 0) {
                    if (strpos($image->uri, 'amazon') !== FALSE) {
                        $programMap[$programID]->imageUri = $image->uri;
                    } else {
                        $programMap[$programID]->imageUri = $guide->apiUrl . '/image/' . $image->uri;
                    }
                    continue 2;
                }
            }
        }
    }
}

$displayArray = [];
foreach ($listings as $listing) {
    $stationArray = [];

    foreach ($listing->programs as $program) {
        if (array_key_exists($program->programID, $programMap)) {
            $programDetails = $programMap[$program->programID];
            $programDetails->airDateTime = $program->airDateTime;
            $programDetails->duration    = $program->duration;
            $stationArray[] = $programDetails;
        }
    }

    if (count($stationArray) > 0) {
        $displayArray[$listing->stationID] = $stationArray;
    }
}

?>

<?php foreach ($displayArray as $stationID => $programs) {
    $station = $guide->stations[$stationID];

    Guide::displayStationTitle($station);

    foreach ($programs as $program) {
        Guide::displayProgram($station, $program);
    }
}
