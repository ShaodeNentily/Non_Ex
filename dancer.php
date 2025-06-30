<?php
require_once 'config.php';

// Aktuelle KW_ID aus Config holen
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

// Dropdown-Daten laden
$services = $pdo->query("SELECT * FROM dancer_service ORDER BY service")->fetchAll();
$counters = $pdo->query("SELECT * FROM counter ORDER BY bezeichnung")->fetchAll();
$kosten = $pdo->query("SELECT * FROM dancer_service_zu_kosten ORDER BY bezeichnung")->fetchAll();
$tanzende = $pdo->query("SELECT * FROM mitarbeiter WHERE position = 'Dancer' ORDER BY name")->fetchAll();

// Eintrag speichern
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kunde = trim($_POST['kunde']);
    $service_id = (int)$_POST['service_id'];
    $anzahl = (int)$_POST['anzahl'];
    $mitarbeiter_id = (int)$_POST['mitarbeiter_id'];
    $gaeste_id = (int)$_POST['gaeste_id'];
    $kosten_id = (int)$_POST['kosten_id'];

    // Dauer aus dem Service holen
    $stmt = $pdo->prepare("SELECT dauer FROM dancer_service WHERE id = ?");
    $stmt->execute([$service_id]);
    $dauer = $stmt->fetchColumn();

    // Ende berechnen (Startzeit jetzt + dauer * anzahl)
    $start = new DateTime();
    list($h, $m) = explode(":", $dauer);
    $gesamtDauer = ($h * 60 + $m) * $anzahl;
    $ende = clone $start;
    $ende->modify("+{$gesamtDauer} minutes");

    // Eintragen
    $stmt = $pdo->prepare("INSERT INTO dancer_bookings (kunde, service_id, anzahl, mitarbeiter_id, zusatz_gaeste_id, zusatz_kosten_id, KW_id, startzeit, endzeit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $kunde,
        $service_id,
        $anzahl,
        $mitarbeiter_id,
        $gaeste_id,
        $kosten_id,
        $current_kw_id,
        $start->format('Y-m-d H:i:s'),
        $ende->format('Y-m-d H:i:s')
    ]);

    header("Location: dancer_buchung.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>💃 Tänzer-Buchung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>💃 Tänzer-Buchung (KW <?= $current_kw_id ?>)</h1>
<form method="post">
    <label for="kunde">Kunde:</label>
    <input type="text" name="kunde" id="kunde" required>

    <label for="service_id">Service:</label>
    <select name="service_id" id="service_id" required>
        <?php foreach ($services as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['service']) ?> (<?= $s['dauer'] ?>, <?= $s['preis'] ?> €)</option>
        <?php endforeach; ?>
    </select>

    <label for="anzahl">Anzahl:</label>
    <select name="anzahl" id="anzahl">
        <?php foreach ($counters as $c): ?>
            <option value="<?= $c['counter'] ?>"><?= htmlspecialchars($c['bezeichnung']) ?> (<?= $c['counter'] ?>)</option>
        <?php endforeach; ?>
    </select>

    <label for="mitarbeiter_id">Tänzer:</label>
    <select name="mitarbeiter_id" id="mitarbeiter_id">
        <?php foreach ($tanzende as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="gaeste_id">Zusätzliche Gäste:</label>
    <select name="gaeste_id" id="gaeste_id">
        <?php foreach ($counters as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['bezeichnung']) ?> (<?= $c['counter'] ?>)</option>
        <?php endforeach; ?>
    </select>

    <label for="kosten_id">Zusatzkosten pro Gast:</label>
    <select name="kosten_id" id="kosten_id">
        <?php foreach ($kosten as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['bezeichnung']) ?> (<?= $k['preis'] ?> €)</option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Buchung speichern">
</form>
</body>
</html>
