<?php
require_once 'config.php';
include 'menu.php';

// Aktuelle KW_ID aus Config
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

// Daten für Dropdowns laden
$services = $pdo->query("SELECT id, art FROM photo_service_art ORDER BY art")->fetchAll();
$counters = $pdo->query("SELECT id, bezeichnung FROM counter ORDER BY bezeichnung")->fetchAll();
$zusatzkosten = $pdo->query("SELECT id, bezeichnung FROM photo_service_zu_kosten ORDER BY bezeichnung")->fetchAll();
$mitarbeiter = $pdo->prepare("SELECT id, name FROM mitarbeiter WHERE position = ? ORDER BY name");
$mitarbeiter->execute(['Photographer']);
$mitarbeiter = $mitarbeiter->fetchAll();

// Eintrag speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['kunde'], $_POST['service_id'], $_POST['zusatz_gast_id'], $_POST['zusatz_kosten_id'], $_POST['mitarbeiter_id'])) {
    $kunde = trim($_POST['kunde']);
    $vip = isset($_POST['vip']) ? 1 : 0;
    $service_id = (int)$_POST['service_id'];
    $zusatz_gast_id = (int)$_POST['zusatz_gast_id'];
    $zusatz_kosten_id = (int)$_POST['zusatz_kosten_id'];
    $mitarbeiter_id = (int)$_POST['mitarbeiter_id'];

    if ($kunde && $service_id && $zusatz_gast_id && $zusatz_kosten_id && $mitarbeiter_id) {
        $stmt = $pdo->prepare("INSERT INTO photoshots (kunde, vip, service_id, zusatz_gast_id, zusatz_kosten_id, mitarbeiter_id, KW_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$kunde, $vip, $service_id, $zusatz_gast_id, $zusatz_kosten_id, $mitarbeiter_id, $current_kw_id]);
        header("Location: photo.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>📷 Photo-Erfassung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>📷 Photo-Eintrag hinzufügen</h1>
    <form method="post">
        <label for="kunde">Kunde:</label>
        <input type="text" name="kunde" id="kunde" required>

        <label for="service_id">Service:</label>
        <select name="service_id" id="service_id" required>
            <?php foreach ($services as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['art']) ?></option>
            <?php endforeach; ?>
        </select>

        <label><input type="checkbox" name="vip"> VIP</label>

        <label for="zusatz_gast_id">Zusätzliche Gäste:</label>
        <select name="zusatz_gast_id" id="zusatz_gast_id" required>
            <?php foreach ($counters as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['bezeichnung']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="zusatz_kosten_id">Zusätzliche Kosten:</label>
        <select name="zusatz_kosten_id" id="zusatz_kosten_id" required>
            <?php foreach ($zusatzkosten as $z): ?>
                <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['bezeichnung']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="mitarbeiter_id">Photographer:</label>
        <select name="mitarbeiter_id" id="mitarbeiter_id" required>
            <?php foreach ($mitarbeiter as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <p><strong>Aktuelle KW-ID:</strong> <?= $current_kw_id ?></p>

        <input type="submit" value="Speichern">
    </form>
</body>
</html>
