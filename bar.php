<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();
}

// Verkauf speichern
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;
$eingetragen_von = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['kunde'], $_POST['getraenke_id'], $_POST['menge'], $_POST['mitarbeiter_id'])) {
    $kunde = trim($_POST['kunde']);
    $getraenke_id = (int)$_POST['getraenke_id'];
    $menge = (int)$_POST['menge'];
    $mitarbeiter_id = (int)$_POST['mitarbeiter_id'];

    if ($kunde && $getraenk_id && $menge > 0 && $mitarbeiter_id) {
        $stmt = $pdo->prepare("INSERT INTO verkaeufe (kunde, getraenke_id, menge, mitarbeiter_id, KW_id,trinkgeld, eingetragen_von) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$kunde, $getraenke_id, $menge, $mitarbeiter_id, $current_kw_id, $trinkgeld, $eingetragen_von]);
        header("Location: bar.php");
        exit;
    }
}


// Getränke abrufen
$getraenke = $pdo->query("SELECT * FROM getraenke ORDER BY name")->fetchAll();


// Barkeeper abrufen
$sql = "
    SELECT m.*
    FROM mitarbeiter m
    JOIN Position p ON m.position = p.id
    WHERE p.name = ?
";
$barkeeper = $pdo->prepare($sql);
$barkeeper->execute(['Bar']);
$barkeeper = $barkeeper->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Barverkauf</title>
</head>
<body>

<h1>Neuer Barverkauf</h1>
<form method="post">
    <label for="kunde">Kundenname:</label>
    <input type="text" name="kunde" id="kunde" required>

    <label for="getraenke_id">Getränk:</label>
    <select name="getraenke_id" id="getraenk_id" required>
        <option value="">– bitte wählen –</option>
        <?php foreach ($getraenke as $g): ?>
            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= $g['preis'] ?>)</option>
        <?php endforeach; ?>
    </select>

    <label for="menge">Menge:</label>
    <input type="number" name="menge" id="menge" min="1" value="1" required>
	
	<label for="trinkgeld">Trinkgeld:</label>
	<input type="number" name="trinkgeld" id="trinkgeld" value="0">

    <label for="mitarbeiter_id">Verkäufer (Bar):</label>
    <select name="mitarbeiter_id" id="mitarbeiter_id" required>
        <option value="">– bitte wählen –</option>
        <?php foreach ($barkeeper as $b): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Verkauf speichern">
</form>

</body>
</html>
