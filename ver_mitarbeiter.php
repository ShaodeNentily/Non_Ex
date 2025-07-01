<?php
include 'menu.php';
require_once 'config.php';


if (!$loggedin && $role !=='admin') {
    header("Location: login.php");
    exit();
}

// Mitarbeiter löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM mitarbeiter WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mitarbeiter aktualisieren
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'], $_POST['edit_name'], $_POST['edit_position'])) {
    $stmt = $pdo->prepare("UPDATE mitarbeiter SET name = ?, position = ? WHERE id = ?");
    $stmt->execute([$_POST['edit_name'], $_POST['edit_position'], $_POST['edit_id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mitarbeiter hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'], $_POST['position']) && !isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("INSERT INTO mitarbeiter (name, position) VALUES (?, ?)");
    $stmt->execute([trim($_POST['name']), trim($_POST['position'])]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mitarbeiter abrufen und gruppieren
$stmt = $pdo->query("SELECT * FROM mitarbeiter ORDER BY position, name");
$mitarbeiterGruppiert = [];
while ($row = $stmt->fetch()) {
    $mitarbeiterGruppiert[$row['position']][] = $row;
}

$positionen = $pdo->query("SELECT id, name FROM Position ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$positionenMap = array_column($positionen, 'name', 'id');

// Prüfe ob Bearbeiten-Modus aktiv ist
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiterliste</title>
</head>
<body>

<h1>Mitarbeiter nach Position</h1>

<?php foreach ($mitarbeiterGruppiert as $posId => $liste): ?>
    <h2><?= htmlspecialchars($positionenMap[$posId] ?? 'Unbekannt') ?></h2>
    <table>
        <tr><th>Name</th><th>Aktion</th></tr>
        <?php foreach ($liste as $m): ?>
            <tr>
                <td>
                    <?php if ($editId === (int)$m['id']): ?>
                        <form method="post" style="display:flex; gap: 8px;">
                            <input type="hidden" name="edit_id" value="<?= $m['id'] ?>">
                            <input type="text" name="edit_name" value="<?= htmlspecialchars($m['name']) ?>" required>
                            <select name="edit_position" required>
                                <?php foreach ($positionen as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $m['position'] == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Speichern</button>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>">Abbrechen</a>
                        </form>
                    <?php else: ?>
                        <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($positionenMap[$m['position']] ?? '-') ?>)
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($editId !== (int)$m['id']): ?>
                        <a href="?edit=<?= $m['id'] ?>">Bearbeiten</a>
                        |
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                            <button type="submit" onclick="return confirm('Wirklich löschen?')">Löschen</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

<h2>Neuen Mitarbeiter hinzufügen</h2>
<form method="post">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" required>

    <label for="position">Position:</label>
    <select name="position" id="position" required>
        <option value="">-- bitte wählen --</option>
        <?php foreach ($positionen as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>
