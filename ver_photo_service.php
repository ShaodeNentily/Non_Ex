<?php
require_once 'config.php';

// Eintrag hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $art = trim($_POST['art']);
    $preis = (int)$_POST['preis'];
    $zusatzgaeste = (int)$_POST['zusatzgaeste'];
    $zusatzpreis = (int)$_POST['zusatzpreis'];

    if ($art && $preis >= 0 && $zusatzgaeste >= 0 && $zusatzpreis >= 0) {
        $stmt = $pdo->prepare("INSERT INTO photo_service (art, preis, zusatzgaeste, zusatzpreis) VALUES (?, ?, ?, ?)");
        $stmt->execute([$art, $preis, $zusatzgaeste, $zusatzpreis]);
        header("Location: photo_service.php");
        exit;
    }
}

// Eintrag bearbeiten
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $art = trim($_POST['edit_art']);
    $preis = (int)$_POST['edit_preis'];
    $zusatzgaeste = (int)$_POST['edit_zusatzgaeste'];
    $zusatzpreis = (int)$_POST['edit_zusatzpreis'];

    $stmt = $pdo->prepare("UPDATE photo_service SET art = ?, preis = ?, zusatzgaeste = ?, zusatzpreis = ? WHERE id = ?");
    $stmt->execute([$art, $preis, $zusatzgaeste, $zusatzpreis, $id]);
    header("Location: photo_service.php");
    exit;
}

// Eintrag löschen
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM photo_service WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: photo_service.php");
    exit;
}

// Einträge laden
$eintraege = $pdo->query("SELECT * FROM photo_service ORDER BY art")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Photo-Service</title>
</head>
<body>

<h1>📸 Photo-Service Verwaltung</h1>

<table>
    <tr>
        <th>Art</th>
        <th>Preis (€)</th>
        <th>Zusätzliche Gäste</th>
        <th>Preis pro Gast (€)</th>
        <th>Aktion</th>
    </tr>
    <?php foreach ($eintraege as $e): ?>
        <tr>
            <form method="post">
                <td><input type="text" name="edit_art" value="<?= htmlspecialchars($e['art']) ?>" required></td>
                <td><input type="number" name="edit_preis" value="<?= $e['preis'] ?>" min="0" required></td>
                <td><input type="number" name="edit_zusatzgaeste" value="<?= $e['zusatzgaeste'] ?>" min="0" required></td>
                <td><input type="number" name="edit_zusatzpreis" value="<?= $e['zusatzpreis'] ?>" min="0" required></td>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $e['id'] ?>">
                    <input type="submit" class="submit-btn" value="💾">
                    <a class="delete-link" href="?delete=<?= $e['id'] ?>" onclick="return confirm('Wirklich löschen?')">🗑️</a>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
</table>

<h2>➕ Eintrag hinzufügen</h2>
<form method="post" class="add-form">
    <input type="hidden" name="add" value="1">
    <input type="text" name="art" placeholder="Art der Dienstleistung" required><br>
    <input type="number" name="preis" placeholder="Basispreis in €" min="0" required><br>
    <input type="number" name="zusatzgaeste" placeholder="Zusätzliche Gäste (max)" min="0" required><br>
    <input type="number" name="zusatzpreis" placeholder="Preis pro zusätzlichem Gast (€)" min="0" required><br>
    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>