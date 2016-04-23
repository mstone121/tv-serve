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
            <video id="player" controls>
                <source src="<?php echo $_GET['video'] ?>" type="video/mp4">
            </video><br>
            <button id="com-destroy">Commercial Destroyer</button>
            <button id="fix">Fix</button>
            <script>
             var video = document.getElementById("player");
             var comF = function() {
                 video.currentTime = video.currentTime + 30;
             };
             var comB = function() {
                 video.currentTime = video.currentTime - 5;
             };
             
             document.getElementById("com-destroy").onclick = comF;
             document.getElementById("fix").onclick = comB;
             document.onkeypress = function(e) {
                 if      (e.charCode == 44) comB();
                 else if (e.charCode == 46) comF();
             }


            </script>
            

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
