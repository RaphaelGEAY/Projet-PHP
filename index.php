<?php
$hello = "World";
?>
<h1>Hello <?php echo $hello ?> !</h1>
<p> <?php echo "Banana Platano" ?> </p>
<ul> <?php echo "VoitiBox" ?> </ul>

<?php
require_once 'db.php';
// Tu peux maintenant utiliser $pdo pour tes requêtes
$query = $pdo->query("SELECT * FROM users");?>
