<!-- TV Serve -->
<!-- Matt Stone -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function displayTable() { ?>
    <h3>TV Serve</h3><br>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th></th>
            </tr
        </thead>
        <tbody>
            <?php foreach (glob('movies/*.mp4') as $video) { ?>
                <tr>
                    <td>
                        <a href="<?php echo "?video=" . urlencode("movies/" . basename($video)); ?>">
                            <?php echo(basename($video)); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo "?video=" . urlencode("movies/" . basename($video)); ?>&delete=yes">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php }

function displayPlayer() { ?>
    <div id="player_container">
        <video id="player" controls>
            <source src="<?php echo $_GET['video'] ?>" type="video/mp4">
        </video><br>
    </div><br><br>

    <button id="commercial-destroy">Commercial Destroyer</button>
    <button id="fix">Fix</button>
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

        <?php
        if (isset($_GET['video'])) {
            if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
                // Delete video
                rename($_GET['video'], "movies/trash/" . substr($_GET['video'], 7));
                header("Location: http://mtv");
                die();
            } else {
                echo '<h3>TV Serve</h3>';
                // Display Player
                displayPlayer();
            } } else {
                // Display Table
                displayTable();
        }

        ?>

    </body>
</html>
