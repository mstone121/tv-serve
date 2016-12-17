<!-- TV Serve -->
<!-- Matt Stone -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function displayTable() { ?>
    <h3>TV Serve</h3><br>
    <table>
        <th>
            <td>File Name</td>
            <td></td>
        </th>
        
        <?php foreach (glob('/tv/movies/*.mp4') as $video) { ?>
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
    </table>
<?php }

function displayPlayer() { ?>
    <video id="player" controls>
        <source src="<?php echo $_GET['video'] ?>" type="video/mp4">
    </video><br>
    
    <button id="com-destroy">Commercial Destroyer</button>
    <button id="fix">Fix</button>
    
    <script>
     var video = document.getElementById("player");
     
     var comF = function() {
         video.currentTime = video.currentTime + 31;
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
<?php } ?>    

<!-- Begin Page -->
<html>
    <head>
        <title>TV Serve</title>
    </head>
    
    <body>

        <?php
        if (isset($_GET['video'])) {
            if (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
                // Delete video
                rename($_GET['video'], "movies/trash/" . substr($_GET['video'], 7));
                header("Location: http://mentoo/tv-serve");
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
