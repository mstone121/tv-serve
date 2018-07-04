<!-- TV Serve -->
<!-- Matt Stone -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LOG_DIR', '/tv/logs');

function displayTable($videoRoot) { ?>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th></th>
            </tr
        </thead>
        <tbody>
            <?php foreach (glob($videoRoot . '/*.mp4') as $video) { ?>
                <tr>
                    <td>
                        <a href="<?php echo "?video=" . urlencode(basename($video)); ?>">
                            <?php echo(basename($video)); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo "?video=" . urlencode(basename($video)); ?>&delete=yes">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php }

function displayGuide() { ?>
    <div id="guide"></div>
    <script type="text/javascript" src="guide.js"></script>
<?php }

function displayPlayer() { ?>
    <div id="player_container">
        <video id="player" controls>
            <source src="movies/<?php echo $_GET['video'] ?>" type="video/mp4">
        </video><br>
    </div><br><br>

    <span class="button" id="commercial-destroy">Commercial Destroyer</span>
    <span class="button" id="fix">Fix</span>
    <span class="button" id="cheat_mode">Cheat Mode</span>

    <div class="log" id="caption_box">
        <input type="range" min="-3" max="3" step="0.1" id="cc_offset"></input>
        <div></div>
    </div>
    <div class="log" id="mplayer_log">
        <h4>mplayer log</h4>
        <pre><?php echo file_get_contents(LOG_DIR . '/' . substr($_GET['video'], 0, -4) . '_record'); ?></pre>
    </div>
    <div class="log" id="avconv_log">
        <h4>avconv log</h4>
        <pre><?php echo file_get_contents(LOG_DIR . '/' . substr($_GET['video'], 0, -4) . '_avconv'); ?></pre>
    </div>
    <div class="log" id="ffmpeg_log">
        <h4>cc log</h4>
        <pre><?php echo file_get_contents(LOG_DIR . '/' . substr($_GET['video'], 0, -4) . '_ffmpeg'); ?></pre>
    </div>

    <?php
    preg_match_all(
        '/^([0-9]{2}):([0-9]{2}):([0-9]{2}),([0-9]{3}) --> ([0-9]{2}):([0-9]{2}):([0-9]{2}),([0-9]{3})\n(.*)\n\s*\n/msU',
        file_get_contents('movies/' . substr($_GET['video'], 0, -4) . '_cc.srt'),
        $matches,
        PREG_SET_ORDER
    );

    $cc = array();

    foreach ($matches as $c) { // (c)aption
        $sTime = (60 * 60 * $c[1]) + (60 * $c[2]) + ($c[3]) + (.001 * $c[4]);
        $eTime = (60 * 60 * $c[5]) + (60 * $c[6]) + ($c[7]) + (.001 * $c[8]);
        $cc[] = array(
            'sTime' => $sTime,
            'eTime' => $eTime,
            'xml' => $c[9]
        );
    }

    ?>
    <div id="subtitles"><?php echo htmlspecialchars(json_encode($cc)); ?></div>
<?php } ?>

<!-- Begin Page -->
<html>
    <head>

        <title>TV Serve</title>

        <!-- Styles -->
        <link href="https://fonts.googleapis.com/css?family=Geostar" rel="stylesheet">
        <link href="styles.css" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="scripts.js"></script>

    </head>

    <body>

        <h3>TV Serve</h3>

        <?php if (isset($_GET['video'])) {
            if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
                // Delete video
                rename("movies/" . $_GET['video'], "movies/trash/" . $_GET['video']);
                rename("movies/" . $_GET['video']. "_cc.srt", "movies/trash/" . $_GET['video'] . "_cc.srt");
                header("Location: http://mtv");
                die();
            } else {
                displayPlayer();
            }
        } else if (isset($_GET['guide']) && $_GET['guide'] === 'yes') {
            displayGuide();
        } else if (isset($_GET['saved']) && $_GET['saved'] === 'yes') {
            displayTable("movies/keep");
        } else {
            displayTable("movies"); ?>
            <a href="?guide=yes">Upcoming...</a>
            <a href="?saved=yes">Saved...</a>
        <?php } ?>

        <p id="footer">Now with <span>SubStation Alpha</span> support!</p>
    </body>
</html>
