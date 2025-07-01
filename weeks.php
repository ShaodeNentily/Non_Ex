<?php
require_once 'config.php'; // PDO-Verbindung
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();
}

$message = '';

// Eintrag hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bezeichnung'])) {
    $bezeichnung = trim($_POST['add_bezeichnung']);
    if ($bezeichnung !== '') {
        $stmt = $pdo->prepare("INSERT INTO config (bezeichnung) VALUES (?)");
        if ($stmt->execute([$bezeichnung])) {
            $message = "Eintrag erfolgreich hinzugefügt.";
        } else {
            $message = "Fehler beim Hinzufügen des Eintrags.";
        }
    } else {
        $message = "Bitte eine gültige Bezeichnung eingeben.";
    }
}

// Eintrag bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'], $_POST['edit_bezeichnung'])) {
    $id = (int)$_POST['edit_id'];
    $bezeichnung = trim($_POST['edit_bezeichnung']);
    if ($bezeichnung !== '') {
        $stmt = $pdo->prepare("UPDATE config SET bezeichnung = ? WHERE id = ?");
        if ($stmt->execute([$bezeichnung, $id])) {
            $message = "Eintrag erfolgreich aktualisiert.";
        } else {
            $message = "Fehler beim Aktualisieren des Eintrags.";
        }
    } else {
        $message = "Bitte eine gültige Bezeichnung eingeben.";
    }
}

// Alle Einträge laden
$stmt = $pdo->query("SELECT * FROM config ORDER BY id");
$configs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Config-Einträge verwalten</title>
    <style>
        table { border-collapse: collapse; width: 50%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        form.inline { display: inline; }
        input[type="text"] { width: 90%; }
    </style>
</head>
<body>

<h1>Config-Einträge verwalten</h1>

<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<h2>Neuen Eintrag hinzufügen</h2>
<form method="post">
    <input type="text" name="add_bezeichnung" required>
    <button type="submit">Hinzufügen</button>
</form>

<h2>Bestehende Einträge</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Bezeichnung</th>
        <th>Aktion</th>
    </tr>
    <?php foreach ($configs as $config): ?>
    <tr>
        <td><?php echo $config['id']; ?></td>
        <td>
            <form method="post" class="inline">
                <input type="hidden" name="edit_id" value="<?php echo $config['id']; ?>">
                <input type="text" name="edit_bezeichnung" value="<?php echo htmlspecialchars($config['bezeichnung']); ?>" required>
        </td>
        <td>
                <button type="submit">Speichern</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
