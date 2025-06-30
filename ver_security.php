<?php
require_once 'config.php';

// Hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $bezeichnung = trim($_POST['bezeichnung']);
    if ($bezeichnung !== "") {
        $stmt = $pdo->prepare("INSERT INTO security_position (bezeichnung) VALUES (?)");
        $stmt->execute([$bezeichnung]);
        header("Location: security_position.php");
        exit;
    }
}

// Bearbeiten
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $bezeichnung = trim($_POST['edit_bezeichnung']);
    if ($bezeichnung !== "") {
        $stmt = $pdo->prepare("UPDATE security_position SET bezeichnung = ? WHERE id = ?");
        $stmt->execute([$bezeichnung, $id]);
        header("Location: security_position.php");
        exit;
    }
}

// Löschen
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM security_position WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: security_position.php");
    exit;
}

// Alle Positionen laden
$positionen = $pdo->query("SELECT * FROM security_position ORDER BY bezeichnung")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>🔐 Security-Positionen</title>
</head>
<body>

<h1>🔐 Security-Positionen verwalten</h1>

<table>
    <tr>
        <th>Bezeichnung</th>
        <th>Aktion</th>
    </tr>
    <?php foreach ($positionen as $pos): ?>
        <tr>
            <form method="post">
                <td>
                    <input type="text" name="edit_bezeichnung" value="<?= htmlspecialchars($pos['bezeichnung']) ?>" required>
                </td>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $pos['id'] ?>">
                    <input type="submit" value="💾 Speichern">
                    <a class="delete-link" href="?delete=<?= $pos['id'] ?>" onclick="return confirm('Wirklich löschen?')">🗑️ Löschen</a>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
</table>

<h2>➕ Neue Position hinzufügen</h2>
<form method="post">
    <input type="hidden" name="add" value="1">
    <input type="text" name="bezeichnung" placeholder="Neue Position" required>
    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>
