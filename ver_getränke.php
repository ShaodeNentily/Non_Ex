<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();

// Getränk löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM getraenke WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Getränk hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'], $_POST['preis'])) {
    $name = trim($_POST['name']);
    $preis = (int)$_POST['preis'];
    if ($name && $preis >= 0) {
        $stmt = $pdo->prepare("INSERT INTO getraenke (name, preis) VALUES (?, ?)");
        $stmt->execute([$name, $preis]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Getränke abrufen
$stmt = $pdo->query("SELECT * FROM getraenke ORDER BY name");
$getraenke = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Getränkeliste</title>
</head>
<body>

<h1>Getränkeliste</h1>

<table>
    <tr><th>Getränk</th><th>Preis (€)</th><th>Aktion</th></tr>
    <?php foreach ($getraenke as $getraenk): ?>
        <tr>
            <td><?php echo htmlspecialchars($getraenk['name']); ?></td>
            <td><?php echo (int)$getraenk['preis']; ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $getraenk['id']; ?>">
                    <button type="submit" class="delete-button" onclick="return confirm('Getränk wirklich löschen?');">Löschen</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Neues Getränk hinzufügen</h2>
<form method="post">
    <label for="name">Getränk:</label>
    <input type="text" name="name" id="name" required>

    <label for="preis">Preis (€):</label>
    <input type="number" name="preis" id="preis" min="0" step="1" required>

    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>
