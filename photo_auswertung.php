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
    WHERE p.KW_id = :kw
    ORDER BY p.datum DESC
	WHERE v.eingetragen_von = :username
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_kw_id]);
$eintraege = $stmt->fetchAll();

$aktuelleKWBezeichnung = '';
foreach ($alleKWs as $kw) {
    if ($kw['id'] == $aktuelleKW) {
        $aktuelleKWBezeichnung = $kw['bezeichnung'];
        break;
    }
}
?>
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>📸 Photo-Auswertung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Photoauswertung – <?= htmlspecialchars($aktuelleKWBezeichnung) ?></h1>

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
    <table>
        <tr>
            <th>Datum</th>
            <th>Kunde</th>
            <th>Service</th>
            <th>VIP</th>
            <th>Zusatzgäste</th>
            <th>Zusatzkosten</th>
            <th>Mitarbeiter</th>
			<th>Eingetragen von</th>
            <th>Summe</th>
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
            <td><?= htmlspecialchars($e['service_art']) ?> (<?= number_format($e['service_preis'], 0) ?></td>
            <td><?= $e['vip'] ? '✅' : '❌' ?></td>
            <td><?= htmlspecialchars($e['gaeste_bez']) ?> (<?= $e['gaeste_anzahl'] ?>)</td>
            <td><?= htmlspecialchars($e['kosten_bez']) ?> (<?= number_format($e['kosten_preis'], 0) ?></td>
            <td><?= htmlspecialchars($e['mitarbeiter_name']) ?></td>
			<td><?= htmlspecialchars($e['eingetragen_von']) ?></td>
            <td><strong><?= number_format($summe, 0) ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
