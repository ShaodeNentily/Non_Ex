<?php
require_once 'config.php';
include 'menu.php';

// Eintrag hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_service'])) {
        $service = trim($_POST['service']);
        $dauer = $_POST['dauer'];
        $preis = (int)$_POST['preis'];
        if ($service && $dauer && $preis >= 0) {
            $stmt = $pdo->prepare("INSERT INTO dancer_service (service, dauer, preis) VALUES (?, ?, ?)");
            $stmt->execute([$service, $dauer, $preis]);
        }
    } elseif (isset($_POST['add_kosten'])) {
        $bezeichnung = trim($_POST['bezeichnung']);
        $preis = (int)$_POST['preis'];
        if ($bezeichnung && $preis >= 0) {
            $stmt = $pdo->prepare("INSERT INTO dancer_service_zu_kosten (bezeichnung, preis) VALUES (?, ?)");
            $stmt->execute([$bezeichnung, $preis]);
        }
    } elseif (isset($_POST['edit_service_id'])) {
        $id = (int)$_POST['edit_service_id'];
        $service = trim($_POST['edit_service']);
        $dauer = $_POST['edit_dauer'];
        $preis = (int)$_POST['edit_preis'];
        $stmt = $pdo->prepare("UPDATE dancer_service SET service = ?, dauer = ?, preis = ? WHERE id = ?");
        $stmt->execute([$service, $dauer, $preis, $id]);
    } elseif (isset($_POST['edit_kosten_id'])) {
        $id = (int)$_POST['edit_kosten_id'];
        $bezeichnung = trim($_POST['edit_bezeichnung']);
        $preis = (int)$_POST['edit_preis'];
        $stmt = $pdo->prepare("UPDATE dancer_service_zu_kosten SET bezeichnung = ?, preis = ? WHERE id = ?");
        $stmt->execute([$bezeichnung, $preis, $id]);
    }
}

// Eintrag löschen
if (isset($_GET['delete_service'])) {
    $stmt = $pdo->prepare("DELETE FROM dancer_service WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_service']]);
} elseif (isset($_GET['delete_kosten'])) {
    $stmt = $pdo->prepare("DELETE FROM dancer_service_zu_kosten WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_kosten']]);
}

// Daten laden
$services = $pdo->query("SELECT * FROM dancer_service ORDER BY service")->fetchAll();
$kosten = $pdo->query("SELECT * FROM dancer_service_zu_kosten ORDER BY bezeichnung")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>🌟 Dancer-Service Verwaltung</title>
</head>
<body>
<h1>🌟 Dancer-Service Verwaltung</h1>
<h2>Services</h2>
<table>
    <tr><th>Service</th><th>Dauer</th><th>Preis (€)</th><th>Aktion</th></tr>
    <?php foreach ($services as $s): ?>
    <tr>
        <form method="post">
            <td><input type="text" name="edit_service" value="<?= htmlspecialchars($s['service']) ?>" required></td>
            <td><input type="time" name="edit_dauer" value="<?= $s['dauer'] ?>" required></td>
            <td><input type="number" name="edit_preis" value="<?= $s['preis'] ?>" min="0" required></td>
            <td>
                <input type="hidden" name="edit_service_id" value="<?= $s['id'] ?>">
                <input type="submit" value="📄">
                <a href="?delete_service=<?= $s['id'] ?>" onclick="return confirm('Wirklich löschen?')">🚮</a>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>
<h3>Neu</h3>
<form method="post">
    <input type="hidden" name="add_service" value="1">
    <input type="text" name="service" placeholder="Service" required>
    <input type="time" name="dauer" required>
    <input type="number" name="preis" placeholder="Preis in €" min="0" required>
    <input type="submit" value="Hinzufügen">
</form>

<h2>Zusatzkosten</h2>
<table>
    <tr><th>Bezeichnung</th><th>Preis (€)</th><th>Aktion</th></tr>
    <?php foreach ($kosten as $k): ?>
    <tr>
        <form method="post">
            <td><input type="text" name="edit_bezeichnung" value="<?= htmlspecialchars($k['bezeichnung']) ?>" required></td>
            <td><input type="number" name="edit_preis" value="<?= $k['preis'] ?>" min="0" required></td>
            <td>
                <input type="hidden" name="edit_kosten_id" value="<?= $k['id'] ?>">
                <input type="submit" value="📄">
                <a href="?delete_kosten=<?= $k['id'] ?>" onclick="return confirm('Wirklich löschen?')">🚮</a>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>
<h3>Neu</h3>
<form method="post">
    <input type="hidden" name="add_kosten" value="1">
    <input type="text" name="bezeichnung" placeholder="Bezeichnung" required>
    <input type="number" name="preis" placeholder="Preis in €" min="0" required>
    <input type="submit" value="Hinzufügen">
</form>
</body>
</html>
