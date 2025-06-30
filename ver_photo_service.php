<?php
require_once 'config.php';

// Einzelnes Feld bearbeiten
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Art bearbeiten
    if (isset($_POST['edit_art'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $art = trim($_POST['edit_art']);
        if ($art !== '') {
            $stmt = $pdo->prepare("UPDATE photo_service SET art = ? WHERE id = ?");
            $stmt->execute([$art, $id]);
            header("Location: photo_service.php");
            exit;
        }
    }
    // Preis bearbeiten
    if (isset($_POST['edit_preis'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $preis = (int)$_POST['edit_preis'];
        if ($preis >= 0) {
            $stmt = $pdo->prepare("UPDATE photo_service SET preis = ? WHERE id = ?");
            $stmt->execute([$preis, $id]);
            header("Location: photo_service.php");
            exit;
        }
    }
    // Zusatzgäste bearbeiten
    if (isset($_POST['edit_zusatzgaeste'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $zusatzgaeste = (int)$_POST['edit_zusatzgaeste'];
        if ($zusatzgaeste >= 0) {
            $stmt = $pdo->prepare("UPDATE photo_service SET zusatzgaeste = ? WHERE id = ?");
            $stmt->execute([$zusatzgaeste, $id]);
            header("Location: photo_service.php");
            exit;
        }
    }
    // Zusatzpreis bearbeiten
    if (isset($_POST['edit_zusatzpreis'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $zusatzpreis = (int)$_POST['edit_zusatzpreis'];
        if ($zusatzpreis >= 0) {
            $stmt = $pdo->prepare("UPDATE photo_service SET zusatzpreis = ? WHERE id = ?");
            $stmt->execute([$zusatzpreis, $id]);
            header("Location: photo_service.php");
            exit;
        }
    }
}

// Eintrag hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $art = trim($_POST['art']);
    $preis = (int)$_POST['preis'];
    $zusatzgaeste = (int)$_POST['zusatzgaeste'];
    $zusatzpreis = (int)$_POST['zusatzpreis'];

    if ($art !== '' && $preis >= 0 && $zusatzgaeste >= 0 && $zusatzpreis >= 0) {
        $stmt = $pdo->prepare("INSERT INTO photo_service (art, preis, zusatzgaeste, zusatzpreis) VALUES (?, ?, ?, ?)");
        $stmt->execute([$art, $preis, $zusatzgaeste, $zusatzpreis]);
        header("Location: photo_service.php");
        exit;
    }
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
            <td>
                <form method="post" style="display:inline;">
                    <input type="text" name="edit_art" value="<?= htmlspecialchars($e['art']) ?>" required>
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <input type="submit" value="💾">
                </form>
            </td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="number" name="edit_preis" value="<?= $e['preis'] ?>" min="0" required>
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <input type="submit" value="💾">
                </form>
            </td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="number" name="edit_zusatzgaeste" value="<?= $e['zusatzgaeste'] ?>" min="0" required>
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <input type="submit" value="💾">
                </form>
            </td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="number" name="edit_zusatzpreis" value="<?= $e['zusatzpreis'] ?>" min="0" required>
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <input type="submit" value="💾">
                </form>
            </td>
            <td>
                <a class="delete-link" href="?delete=<?= $e['id'] ?>" onclick="return confirm('Wirklich löschen?')">🗑️</a>
            </td>
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
