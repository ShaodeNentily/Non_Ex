<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();

// Mitarbeiter löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM mitarbeiter WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mitarbeiter hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'], $_POST['position'])) {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    if ($name && $position) {
        $stmt = $pdo->prepare("INSERT INTO mitarbeiter (name, position) VALUES (?, ?)");
        $stmt->execute([$name, $position]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Mitarbeiter abrufen und nach Position gruppieren
$stmt = $pdo->query("SELECT * FROM mitarbeiter ORDER BY position, name");
$mitarbeiterGruppiert = [];

while ($row = $stmt->fetch()) {
    $mitarbeiterGruppiert[$row['position']][] = $row;
}
$positionen = $pdo->query("SELECT id, name FROM Position ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiterliste</title>
</head>
<body>

<h1>Mitarbeiter nach Position</h1>

<?php foreach ($mitarbeiterGruppiert as $position => $mitarbeiterListe): ?>
    <h2><?php echo htmlspecialchars($position); ?></h2>
    <table>
        <tr><th>Name</th><th>Aktion</th></tr>
        <?php foreach ($mitarbeiterListe as $mitarbeiter): ?>
            <tr>
                <td><?php echo htmlspecialchars($mitarbeiter['name']); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $mitarbeiter['id']; ?>">
                        <button type="submit" class="delete-button" onclick="return confirm('Mitarbeiter wirklich löschen?');">Löschen</button>
                    </form>
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
        <?php foreach ($positionen as $pos): ?>
            <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>
