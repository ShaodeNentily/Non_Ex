<?php
require_once 'config.php';

// Aktuelle KW_ID aus Config
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

// Daten abrufen
$sql = "
    SELECT p.*, 
           s.art AS service_art, s.preis AS service_preis, 
           c.bezeichnung AS gaeste_bez, c.counter AS gaeste_anzahl,
           z.bezeichnung AS kosten_bez, z.preis AS kosten_preis,
           m.name AS mitarbeiter_name
    FROM photoshots p
    JOIN photo_service_art s ON p.service_id = s.id
    JOIN counter c ON p.zusatz_gast_id = c.id
    JOIN photo_service_zu_kosten z ON p.zusatz_kosten_id = z.id
    JOIN mitarbeiter m ON p.mitarbeiter_id = m.id
    WHERE p.KW_id = ?
    ORDER BY p.datum DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_kw_id]);
$eintraege = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>📸 Photo-Auswertung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>📸 Photo-Auswertung (KW <?= $current_kw_id ?>)</h1>
    <table>
        <tr>
            <th>Datum</th>
            <th>Kunde</th>
            <th>Service</th>
            <th>VIP</th>
            <th>Zusatzgäste</th>
            <th>Zusatzkosten</th>
            <th>Mitarbeiter</th>
            <th>Summe (€)</th>
        </tr>
        <?php foreach ($eintraege as $e):
            $basispreis = $e['service_preis'];
            if ($e['vip']) {
                $basispreis *= 0.8;
            }
            $zusatzkosten = $e['gaeste_anzahl'] * $e['kosten_preis'];
            $summe = $basispreis + $zusatzkosten;
        ?>
        <tr>
            <td><?= date("d.m.Y H:i", strtotime($e['datum'])) ?></td>
            <td><?= htmlspecialchars($e['kunde']) ?></td>
            <td><?= htmlspecialchars($e['service_art']) ?> (<?= number_format($e['service_preis'], 2) ?> €)</td>
            <td><?= $e['vip'] ? '✅' : '❌' ?></td>
            <td><?= htmlspecialchars($e['gaeste_bez']) ?> (<?= $e['gaeste_anzahl'] ?>)</td>
            <td><?= htmlspecialchars($e['kosten_bez']) ?> (<?= number_format($e['kosten_preis'], 2) ?> €)</td>
            <td><?= htmlspecialchars($e['mitarbeiter_name']) ?></td>
            <td><strong><?= number_format($summe, 2) ?> €</strong></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
