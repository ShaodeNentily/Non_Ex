<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin && $role !=='admin') {
    header("Location: login.php");
    exit();
}

// Verarbeitung aller Tabellen je nach Formular

// --- photo_service_art ---
if (isset($_POST['add_art'])) {
    $art = trim($_POST['art']);
    $preis = (int)$_POST['preis'];
    if ($art !== '' && $preis >= 0) {
        $stmt = $pdo->prepare("INSERT INTO photo_service_art (art, preis) VALUES (?, ?)");
        $stmt->execute([$art, $preis]);
    }
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_POST['edit_art_id'])) {
    $id = (int)$_POST['edit_art_id'];
    $art = trim($_POST['edit_art']);
    $preis = (int)$_POST['edit_preis'];
    $stmt = $pdo->prepare("UPDATE photo_service_art SET art = ?, preis = ? WHERE id = ?");
    $stmt->execute([$art, $preis, $id]);
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_GET['delete_art'])) {
    $stmt = $pdo->prepare("DELETE FROM photo_service_art WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_art']]);
    header("Location: ver_photo_service.php"); exit;
}

// --- counter ---
if (isset($_POST['add_counter'])) {
    $bez = trim($_POST['counter_bez']);
    $val = (int)$_POST['counter_val'];
    if ($bez !== '') {
        $stmt = $pdo->prepare("INSERT INTO counter (bezeichnung, counter) VALUES (?, ?)");
        $stmt->execute([$bez, $val]);
    }
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_POST['edit_counter_id'])) {
    $id = (int)$_POST['edit_counter_id'];
    $bez = trim($_POST['edit_counter_bez']);
    $val = (int)$_POST['edit_counter_val'];
    $stmt = $pdo->prepare("UPDATE counter SET bezeichnung = ?, counter = ? WHERE id = ?");
    $stmt->execute([$bez, $val, $id]);
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_GET['delete_counter'])) {
    $stmt = $pdo->prepare("DELETE FROM counter WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_counter']]);
    header("Location: ver_photo_service.php"); exit;
}

// --- photo_service_zu_kosten ---
if (isset($_POST['add_zk'])) {
    $bez = trim($_POST['zk_bez']);
    $preis = (int)$_POST['zk_preis'];
    if ($bez !== '') {
        $stmt = $pdo->prepare("INSERT INTO photo_service_zu_kosten (bezeichnung, preis) VALUES (?, ?)");
        $stmt->execute([$bez, $preis]);
    }
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_POST['edit_zk_id'])) {
    $id = (int)$_POST['edit_zk_id'];
    $bez = trim($_POST['edit_zk_bez']);
    $preis = (int)$_POST['edit_zk_preis'];
    $stmt = $pdo->prepare("UPDATE photo_service_zu_kosten SET bezeichnung = ?, preis = ? WHERE id = ?");
    $stmt->execute([$bez, $preis, $id]);
    header("Location: ver_photo_service.php"); exit;
}

if (isset($_GET['delete_zk'])) {
    $stmt = $pdo->prepare("DELETE FROM photo_service_zu_kosten WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_zk']]);
    header("Location: ver_photo_service.php"); exit;
}

// Daten laden
$art_list = $pdo->query("SELECT * FROM photo_service_art ORDER BY art")->fetchAll();
$counter_list = $pdo->query("SELECT * FROM counter ORDER BY bezeichnung")->fetchAll();
$zk_list = $pdo->query("SELECT * FROM photo_service_zu_kosten ORDER BY bezeichnung")->fetchAll();
$rooms_list = $pdo->query("SELECT * FROM rooms ORDER BY bezeichnung")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Verwaltung Photo-Service</title>
</head>
<body>
<h1>📸 Photo-Service Verwaltung</h1>

<h2>Art + Preis</h2>
<table>
<tr><th>Art</th><th>Preis</th><th>Aktion</th></tr>
<?php foreach ($art_list as $row): ?>
<tr>
  <form method="post">
    <td><input type="text" name="edit_art" value="<?= htmlspecialchars($row['art']) ?>" required></td>
    <td><input type="number" name="edit_preis" value="<?= $row['preis'] ?>" required></td>
    <td>
      <input type="hidden" name="edit_art_id" value="<?= $row['id'] ?>">
      <input type="submit" value="💾">
      <a href="?delete_art=<?= $row['id'] ?>" onclick="return confirm('Löschen?')">🗑️</a>
    </td>
  </form>
</tr>
<?php endforeach; ?>
<tr>
  <form method="post">
    <td><input type="text" name="art" required></td>
    <td><input type="number" name="preis" required></td>
    <td><input type="submit" name="add_art" value="➕ Hinzufügen"></td>
  </form>
</tr>
</table>

<h2>Counter</h2>
<table>
<tr><th>Bezeichnung</th><th>Wert</th><th>Aktion</th></tr>
<?php foreach ($counter_list as $row): ?>
<tr>
  <form method="post">
    <td><input type="text" name="edit_counter_bez" value="<?= htmlspecialchars($row['bezeichnung']) ?>" required></td>
    <td><input type="number" name="edit_counter_val" value="<?= $row['counter'] ?>" required></td>
    <td>
      <input type="hidden" name="edit_counter_id" value="<?= $row['id'] ?>">
      <input type="submit" value="💾">
      <a href="?delete_counter=<?= $row['id'] ?>" onclick="return confirm('Löschen?')">🗑️</a>
    </td>
  </form>
</tr>
<?php endforeach; ?>
<tr>
  <form method="post">
    <td><input type="text" name="counter_bez" required></td>
    <td><input type="number" name="counter_val" required></td>
    <td><input type="submit" name="add_counter" value="➕ Hinzufügen"></td>
  </form>
</tr>
</table>

<h2>Zusatzkosten</h2>
<table>
<tr><th>Bezeichnung</th><th>Preis</th><th>Aktion</th></tr>
<?php foreach ($zk_list as $row): ?>
<tr>
  <form method="post">
    <td><input type="text" name="edit_zk_bez" value="<?= htmlspecialchars($row['bezeichnung']) ?>" required></td>
    <td><input type="number" name="edit_zk_preis" value="<?= $row['preis'] ?>" required></td>
    <td>
      <input type="hidden" name="edit_zk_id" value="<?= $row['id'] ?>">
      <input type="submit" value="💾">
      <a href="?delete_zk=<?= $row['id'] ?>" onclick="return confirm('Löschen?')">🗑️</a>
    </td>
  </form>
</tr>
<?php endforeach; ?>
<tr>
  <form method="post">
    <td><input type="text" name="zk_bez" required></td>
    <td><input type="number" name="zk_preis" required></td>
    <td><input type="submit" name="add_zk" value="➕ Hinzufügen"></td>
  </form>
</tr>
</table>
<h2>Räume</h2>
<table>
<tr><th>Bezeichnung</th><th>Aktion</th></tr>
<?php foreach ($rooms_list as $room): ?>
<tr>
  <form method="post">
    <td>
      <input type="text" name="edit_room_bez" value="<?= htmlspecialchars($room['bezeichnung']) ?>" required>
    </td>
    <td>
      <input type="hidden" name="edit_room_id" value="<?= $room['id'] ?>">
      <input type="submit" value="💾">
      <a href="?delete_room=<?= $room['id'] ?>" onclick="return confirm('Diesen Raum wirklich löschen?')">🗑️</a>
    </td>
  </form>
</tr>
<?php endforeach; ?>
<tr>
  <form method="post">
    <td><input type="text" name="room_bez" required></td>
    <td><input type="submit" name="add_room" value="➕ Hinzufügen"></td>
  </form>
</tr>
</table>
</body>
</html>
