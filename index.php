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
    </table>
<?php }

function displayPlayer() { ?>
    <video id="player" controls>
        <source src="<?php echo $_GET['video'] ?>" type="video/mp4">
    </video><br>
    
    <button id="commercial-destroy">Commercial Destroyer</button>
    <button id="fix">Fix</button>
    
    <script>
     const video = document.getElementById("player");
     
     const commercialBack = function() {
         video.currentTime = video.currentTime - 5;
     };
     const commercialDestroy = function() {
         video.currentTime = video.currentTime + 31;
     };
     };
     
     document.getElementById("commercial-destroy").onclick = commercialDestroy;
     document.getElementById("fix").onclick = commercialBack;
     
     document.onkeypress = function({ charCode: code }) {
         if      (code === 44) commercialBack();
         else if (code === 46) commercialDestroy();
         
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
                header("Location: http://mtv/tv-serve");
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
