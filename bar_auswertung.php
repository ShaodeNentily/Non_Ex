<?php
require_once 'config.php';
include 'menu.php';

// Verkäufe mit Preisen, Getränken, Mitarbeitern laden
$sql = "
    SELECT v.*, g.getraenk, g.preis, m.name AS mitarbeiter_name
        FROM verkauf v
        JOIN getraenk g ON v.getraenk_id = g.id
        JOIN mitarbeiter m ON v.mitarbeiter_id = m.id
        WHERE v.KW_id = (
            SELECT MAX(KW_id) FROM verkauf
)
ORDER BY v.id DESC
";
$verkaeufe = $pdo->query($sql)->fetchAll();

$gesamtSumme = 0;
$mitarbeiterSummen = []; // ID → Summe
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Barverkaufsübersicht</title>
</head>
<body>

<h1>Alle Barverkäufe</h1>

<table>
    <tr>
        <th>Datum</th>
        <th>Kunde</th>
        <th>Getränk</th>
        <th>Preis</th>
        <th>Menge</th>
        <th>Gesamt</th>
        <th>Verkäufer</th>
    </tr>
    <?php foreach ($verkaeufe as $v): 
        $gesamt = $v['preis'] * $v['menge'];
        $gesamtSumme += $gesamt;

        // Summen je Mitarbeiter
        if (!isset($mitarbeiterSummen[$v['mitarbeiter_id']])) {
            $mitarbeiterSummen[$v['mitarbeiter_id']] = [
                'name' => $v['mitarbeiter_name'],
                'summe' => 0
            ];
        }
        $mitarbeiterSummen[$v['mitarbeiter_id']]['summe'] += $gesamt;
    ?>
        <tr>
            <td><?= date('d.m.Y H:i', strtotime($v['datum'])) ?></td>
            <td><?= htmlspecialchars($v['kunde']) ?></td>
            <td><?= htmlspecialchars($v['getraenk_name']) ?></td>
            <td><?= $v['preis'] ?></td>
            <td><?= $v['menge'] ?></td>
            <td><?= $gesamt ?></td>
            <td><?= htmlspecialchars($v['mitarbeiter_name']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<p class="summe">💰 Gesamtsumme aller Verkäufe: <strong><?= $gesamtSumme ?> Gil</strong></p>

<div class="mitarbeiter-summen">
    <h2>📊 Verkäufe nach Mitarbeiter</h2>
    <ul>
        <?php foreach ($mitarbeiterSummen as $m): ?>
            <li><strong><?= htmlspecialchars($m['name']) ?>:</strong> <?= $m['summe'] ?> Gil</li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
