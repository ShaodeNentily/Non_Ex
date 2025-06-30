<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();

// Aktuelle KW_ID aus Config holen
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

// Daten abrufen
$stmt = $pdo->prepare("
    SELECT b.*, ds.service, ds.dauer, ds.preis AS service_preis,
           m.name AS mitarbeiter_name,
           c.counter AS zusatz_anzahl,
           dk.preis AS kosten_pro_gast
    FROM dancer_bookings b
    JOIN dancer_service ds ON b.service_id = ds.id
    JOIN mitarbeiter m ON b.mitarbeiter_id = m.id
    JOIN counter c ON b.zusatz_gaeste_id = c.id
    JOIN dancer_service_zu_kosten dk ON b.zusatz_kosten_id = dk.id
    WHERE b.KW_id = ?
    ORDER BY b.startzeit DESC
");
$stmt->execute([$current_kw_id]);
$buchungen = $stmt->fetchAll();

function berechne_summe($service_preis, $anzahl, $vip = false, $gaeste = 0, $kosten_pro_gast = 0) {
    $gesamt = $service_preis * $anzahl;
    if ($vip) $gesamt *= 0.8;
    $gesamt += $gaeste * $kosten_pro_gast;
    return $gesamt;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>💃 Tänzer Auswertung (KW <?= $current_kw_id ?>)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>💃 Tänzer Auswertung (KW <?= $current_kw_id ?>)</h1>
<table>
    <tr>
        <th>Kunde</th>
        <th>Service</th>
        <th>Anzahl</th>
        <th>Mitarbeiter</th>
        <th>Zus. Gäste</th>
        <th>Kosten/Gast</th>
        <th>Start</th>
        <th>Ende</th>
        <th>VIP</th>
        <th>Summe €</th>
    </tr>
    <?php foreach ($buchungen as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['kunde']) ?></td>
            <td><?= htmlspecialchars($b['service']) ?></td>
            <td><?= $b['anzahl'] ?></td>
            <td><?= htmlspecialchars($b['mitarbeiter_name']) ?></td>
            <td><?= $b['zusatz_anzahl'] ?></td>
            <td><?= $b['kosten_pro_gast'] ?> €</td>
            <td><?= $b['startzeit'] ?></td>
            <td><?= $b['endzeit'] ?></td>
            <td><?= $b['vip'] ? '✅' : '❌' ?></td>
            <td>
                <?php
                $summe = berechne_summe(
                    $b['service_preis'],
                    $b['anzahl'],
                    $b['vip'],
                    $b['zusatz_anzahl'],
                    $b['kosten_pro_gast']
                );
                echo number_format($summe, 2, ',', '.');
                ?> €
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
