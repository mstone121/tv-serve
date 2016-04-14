<!-- TV Serve -->
<!-- Matt Stone -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<html>
    <head>

    </head>

    <body>
        <h3>TV Serve</h3><br>

        <?php if (isset($_GET['video'])) { ?>
            <!-- Display Player -->
            <video controls>
                <source src="<?php echo $_GET['video'] ?>" type="video/mp4">
            </video>

        <?php } else { ?>
            <!-- Display Table -->
            <table>
                <th>
                    <td>File Name</td>
                </th>

                <?php foreach (glob('/tv/movies/*.mp4') as $video) { ?>
                    <tr>
                        <td>
                            <a href="<?php echo "?video=" . urlencode("movies/" . basename($video)); ?>">
                                <?php echo(basename($video)); ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>

            </table>
        <?php } ?>
    </body>
</html>
