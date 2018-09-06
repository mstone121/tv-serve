<?php

if ($_SERVER['REMOTE_HOST'] !== 'mosx') {
    // you are a loser (i.e. not me)
    exit;
}

$config = json_decode(file_get_contents('../guideConfig.json'));

$callsign = $_GET['callsign'];
$station = $config->stationMap->$callsign;

$airTime = new DateTime($_GET['airTime']);
$airTime->setTimezone(new DateTimeZone('America/Chicago'));
$airTime = $airTime->format('h:i Y-m-d');

$duration = $_GET['duration'];

$title = preg_replace('/[^A-Za-z0-9\s]/', '', $_GET['title']);
$title = preg_replace('/\s+/', '_', $title);

$execString = "at $airTime <<< 'record $station {$duration}s $title' 2>&1";

$output = shell_exec($execString);
echo "Exec: $execString" . PHP_EOL;
echo "Output: " . PHP_EOL . $output;
