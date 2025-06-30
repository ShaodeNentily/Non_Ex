<?php
require_once 'config.php';

// Verkauf speichern
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['kunde'], $_POST['service_id'], $_POST['anzahl'], $_POST['mitarbeiter_id'])) {
    $kunde = trim($_POST['kunde']);
    service = (int)$_POST['service_id'];
    $anzahl = (int)$_POST['anzahl'];
    $mitarbeiter_id = (int)$_POST['mitarbeiter_id'];

    if ($kunde && $service_id && $menge > 0 && $mitarbeiter_id) {
        $stmt = $pdo->prepare("INSERT INTO dancer_dienste (kunde, service_id, vip, anzahl,dauer_id, mitarbeiter_id, zusatz_gast, zusatz_gast_price, KW_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$kunde, $service_id, $vip, $anzahl, $dauer_id,  $mitarbeiter_id, $zusatz_gast,$zusatz_gast_price,  $current_kw_id]);
        header("Location: photo.php");
        exit;
    }
}


// Getränke abrufen
$service = $pdo->query("SELECT * FROM dancer_service ORDER BY name")->fetchAll();


// Barkeeper abrufen
dancer = $pdo->prepare("SELECT * FROM mitarbeiter WHERE position = ?");
$dancer->execute(['Dancer']);
$dancer = $dancer->fetchAll();
?>
