<?php
include 'config.php';

$username = isset($_GET['username']) ? $_GET['username'] : null;

$vod_data = null;
$error = null;

if ($username) {
    $url = 'https://api.twitch.tv/helix/users?login='.$username;
    $user_data = request($url);

    if (isset($user_data['data'][0]['id'])) {
        $user_id = $user_data['data'][0]['id'];
        $url = 'https://api.twitch.tv/helix/videos?user_id='.$user_id;
        $vod_data = request($url);
    } else {
        $error = "Le nom d'utilisateur n'existe pas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Twitch VOD Search</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="search-container">
        <form action="index.php" method="get">
            <input type="text" name="username" placeholder="Nom d'utilisateur Twitch" value="<?php echo htmlspecialchars($username); ?>" required>
            <button type="submit">Valider</button>
        </form>
    </div>

    <?php if ($error) : ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <table id="vodTable" style="display: none;">
    <tr>
        <th>ID de Vidéo</th>
        <th>Date</th>
        <th>Titre de la VOD</th>
        <th>Durée</th>
        <th>Télécharger</th>
    </tr>


    <?php if ($vod_data) : ?>
        <?php foreach($vod_data['data'] as $vod) : ?>
            <tr class="vod-row">
                <td class="copy-id"><?php echo $vod['id']; ?></td>
                <td><?php 
                    $date = new DateTime($vod['created_at']); 
                    $date->setTimezone(new DateTimeZone('Europe/Paris')); 
                    echo $date->format('d/m/Y H:i');
                ?></td>
                <td><?php echo $vod['title']; ?></td>
                <td><?php echo $vod['duration']; ?></td>
                <td class="download-button" data-href="../TwitchDownloadInterface/index.php?id=<?php echo $vod['id']; ?>">Télécharger</td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<script>
    // Si des données de VOD existent, affichez le tableau
    <?php if ($vod_data): ?>
        document.getElementById('vodTable').style.display = 'table';
    <?php endif; ?>

    // Fonction pour copier l'ID de la vidéo dans le presse-papier
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
        element.classList.add("blink");
        setTimeout(function() {
            element.classList.remove("blink");
        }, 1000);
    }



    document.addEventListener('DOMContentLoaded', function() {
        var clickableCells = document.querySelectorAll('td[data-href]');
        clickableCells.forEach(function(cell) {
            cell.addEventListener('click', function() {
                window.open(this.dataset.href);
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var clickableCells = document.querySelectorAll('td.copy-id');
        clickableCells.forEach(function(cell) {
            cell.addEventListener('click', function() {
                copyToClipboard(this);
            });
        });
    });
</script>
</body>
</html>
