<?php

if ($_SERVER['REMOTE_HOST'] !== 'mosx') {
    // you are a loser (i.e. not me)
    exit;
}

if (strpos($_SERVER['REMOTE_ADDR'], '10.0.0') !== 0) {
    // man, you're not even local
    exit;
}

$config = json_decode(file_get_contents('../guideConfig.json'));

$callsign = $_GET['callsign'];
$station = $config->stationMap->$callsign;

$airTime = new DateTime($_GET['airTime']);
$airTime->setTimezone(new DateTimeZone('America/Chicago'));
$airTime = $airTime->format('H:i Y-m-d');

$duration = intval($_GET['duration']) + 60;

$title = preg_replace('/[^A-Za-z0-9\s]/', '', $_GET['title']);
$title = preg_replace('/\s+/', '_', $title);

//  <<< 'record $station {$duration}s $title' 2>&1
$resource = proc_open("at $airTime", [ ['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w'] ], $pipes);

if (is_resource($resource)) {
    fwrite($pipes[0], "record $station {$duration}s $title");
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    $error  = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $return_value = proc_close($process);

    print json_encode([
        'return_value' => $return_value,
        'output' => $output,
        'error'  => $error
    ], JSON_PRETTY_PRINT);
} else {
    echo 'Creating resource failed:' . $resource;
}
