<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();
}

// Kalenderwochen (KW) aus der config-Tabelle holen
$alleKWs = $pdo->query("SELECT id, bezeichnung FROM config ORDER BY id DESC")->fetchAll();

// Aktuelle KW (höchste ID) oder vom User gewählte KW
$aktuelleKW = isset($_GET['kw']) && ctype_digit($_GET['kw']) ? (int)$_GET['kw'] : $alleKWs[0]['id'];

// Verkäufe dieser KW abrufen
$sql = "
    SELECT v.*, g.getraenke AS getraenke_name, g.preis, m.name AS mitarbeiter_name
    FROM verkaeufe v
    JOIN getraenke g ON v.getraenke_id = g.id
    JOIN mitarbeiter m ON v.mitarbeiter_id = m.id
    WHERE v.KW_id = :kw
    ORDER BY v.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['kw' => $aktuelleKW]);
$verkaeufe = $stmt->fetchAll();

$gesamtSumme = 0;
$mitarbeiterSummen = [];

$aktuelleKWBezeichnung = '';
foreach ($alleKWs as $kw) {
    if ($kw['id'] == $aktuelleKW) {
        $aktuelleKWBezeichnung = $kw['bezeichnung'];
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Barverkaufsübersicht</title>
</head>
<body>

<h1>Barverkäufe – <?= htmlspecialchars($aktuelleKWBezeichnung) ?></h1>

<form method="get" style="margin-bottom: 1em;">
    <label for="kw">Kalenderwoche wählen:</label>
    <select name="kw" id="kw" onchange="this.form.submit()">
        <?php foreach ($alleKWs as $kw): ?>
            <option value="<?= $kw['id'] ?>" <?= $kw['id'] == $aktuelleKW ? 'selected' : '' ?>>
                <?= htmlspecialchars($kw['bezeichnung']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table border="1" cellpadding="5">
    <tr>
        <th>Datum</th>
        <th>Kunde</th>
        <th>Getränk</th>
        <th>Preis</th>
        <th>Menge</th>
        <th>Gesamt</th>
        <th>Verkäufer</th>
        <th>Eingetragen von</th>
    </tr>
    <?php foreach ($verkaeufe as $v): 
        $gesamt = $v['preis'] * $v['menge'];
        $gesamtSumme += $gesamt;

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
            <td><?= htmlspecialchars($v['getraenke_name']) ?></td>
            <td><?= $v['preis'] ?></td>
            <td><?= $v['menge'] ?></td>
            <td><?= $gesamt ?></td>
            <td><?= htmlspecialchars($v['mitarbeiter_name']) ?></td>
            <td><?= htmlspecialchars($v['eingetragen_von']) ?></td>
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