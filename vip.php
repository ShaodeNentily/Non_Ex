<?php
include 'config.php';
include 'menu.php';
if (!$loggedin) {
    header("Location: login.php");
    exit();
}

// Initialisieren
$edit_mode = false;
$edit_vip = null;
$eingetragen_von = $_SESSION['username'];

// Bearbeiten vorbereiten
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM VIP WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_vip = $stmt->fetch();
    if ($edit_vip) {
        $edit_mode = true;
    }
}

// VIP löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM VIP WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    echo "<p style='color: red;'>VIP wurde gelöscht.</p>";
}

// VIP bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_vip'])) {
    $stmt = $pdo->prepare("UPDATE VIP SET 
        name = ?, tier_id = ?, line_skip = ?, free_drink1 = ?, free_drink2 = ?, 
        lapdance = ?, photoshot = ?, preis_id = ?, mitarbeiter_id = ?, kw = ?
        WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['tier_id'],
        isset($_POST['line_skip']) ? 1 : 0,
        isset($_POST['free_drink1']) ? 1 : 0,
        isset($_POST['free_drink2']) ? 1 : 0,
        isset($_POST['lapdance']) ? 1 : 0,
        isset($_POST['photoshot']) ? 1 : 0,
        $_POST['preis_id'],
        $_POST['mitarbeiter_id'],
        $_POST['kw'],
        $_POST['id']
    ]);
    echo "<p style='color: green;'>VIP wurde aktualisiert.</p>";
    $edit_mode = false;
    $edit_vip = null;
}

// VIP hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vip'])) {
    $stmt = $pdo->prepare("INSERT INTO VIP 
        (name, tier_id, line_skip, free_drink1, free_drink2, lapdance, photoshot, preis_id, mitarbeiter_id, kw, eingetragen_von)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['tier_id'],
        isset($_POST['line_skip']) ? 1 : 0,
        isset($_POST['free_drink1']) ? 1 : 0,
        isset($_POST['free_drink2']) ? 1 : 0,
        isset($_POST['lapdance']) ? 1 : 0,
        isset($_POST['photoshot']) ? 1 : 0,
        $_POST['preis_id'],
        $_POST['mitarbeiter_id'],
        $_POST['kw'],
		$_SESSION['username'] 
    ]);
    echo "<p style='color: green;'>VIP wurde erfolgreich hinzugefügt.</p>";
}

// Daten für Dropdowns
$tiers = $pdo->query("SELECT id, wert FROM VIP_config WHERE typ='tier'")->fetchAll();
$preise = $pdo->query("SELECT id, wert FROM VIP_config WHERE typ='preis'")->fetchAll();
$mitarbeiter = $pdo->query("SELECT id, name FROM mitarbeiter")->fetchAll();
$eintraege = $pdo->query("
    SELECT v.*, 
           t.wert AS tier, 
           p.wert AS preis, 
           m.name AS mitarbeiter
    FROM VIP v
    LEFT JOIN VIP_config t ON v.tier_id = t.id
    LEFT JOIN VIP_config p ON v.preis_id = p.id
    LEFT JOIN mitarbeiter m ON v.mitarbeiter_id = m.id
    ORDER BY v.erstellt_am DESC
")->fetchAll();
?>

<h2><?= $edit_mode ? "VIP bearbeiten" : "VIP hinzufügen" ?></h2>
<form method="post">
    <?php if ($edit_mode): ?>
        <input type="hidden" name="edit_vip" value="1">
        <input type="hidden" name="id" value="<?= $edit_vip['id'] ?>">
    <?php else: ?>
        <input type="hidden" name="add_vip" value="1">
    <?php endif; ?>

    <label>Name:</label><br>
    <input type="text" name="name" required value="<?= $edit_mode ? htmlspecialchars($edit_vip['name']) : '' ?>"><br><br>

    <label>VIP-Tier:</label><br>
    <select name="tier_id" required>
        <?php foreach ($tiers as $tier): ?>
            <option value="<?= $tier['id'] ?>" <?= $edit_mode && $edit_vip['tier_id'] == $tier['id'] ? 'selected' : '' ?>><?= $tier['wert'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label><input type="checkbox" name="line_skip" <?= $edit_mode && $edit_vip['line_skip'] ? 'checked' : '' ?>> Line Skip</label><br>
    <label><input type="checkbox" name="free_drink1" <?= $edit_mode && $edit_vip['free_drink1'] ? 'checked' : '' ?>> Free Drink 1</label><br>
    <label><input type="checkbox" name="free_drink2" <?= $edit_mode && $edit_vip['free_drink2'] ? 'checked' : '' ?>> Free Drink 2</label><br>
    <label><input type="checkbox" name="lapdance" <?= $edit_mode && $edit_vip['lapdance'] ? 'checked' : '' ?>> Lapdance</label><br>
    <label><input type="checkbox" name="photoshot" <?= $edit_mode && $edit_vip['photoshot'] ? 'checked' : '' ?>> Fotoshooting</label><br><br>

    <label>Preis:</label><br>
    <select name="preis_id" required>
        <?php foreach ($preise as $preis): ?>
            <option value="<?= $preis['id'] ?>" <?= $edit_mode && $edit_vip['preis_id'] == $preis['id'] ? 'selected' : '' ?>><?= $preis['wert'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Mitarbeiter:</label><br>
    <select name="mitarbeiter_id" required>
		<?php foreach ($mitarbeiter as $m): ?>
			<option value="<?= $m['id'] ?>" <?= $edit_mode && $edit_vip['mitarbeiter_id'] == $m['id'] ? 'selected' : '' ?>>
				<?= htmlspecialchars($m['name']) ?>
			</option>
		<?php endforeach; ?>
	</select><br><br>

    <label>Kalenderwoche (KW):</label><br>
    <input type="number" name="kw" min="1" max="53" value="<?= $edit_mode ? $edit_vip['kw'] : date('W') ?>"><br><br>

    <input type="submit" value="<?= $edit_mode ? 'Änderungen speichern' : 'VIP speichern' ?>">
    <?php if ($edit_mode): ?>
        <a href="vip.php"><button type="button">Abbrechen</button></a>
    <?php endif; ?>
</form>

<hr>

<h2>Bestehende VIPs</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>Tier</th>
        <th>Preis</th>
        <th>Mitarbeiter</th>
        <th>Extras</th>
        <th>KW</th>
        <th>Datum</th>
        <th>Aktionen</th>
    </tr>
    <?php foreach ($eintraege as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['name']) ?></td>
            <td><?= $v['tier'] ?></td>
            <td><?= $v['preis'] ?></td>
            <td><?= $v['mitarbeiter'] ?></td>
            <td>
                <?= $v['line_skip'] ? 'Line Skip<br>' : '' ?>
                <?= $v['free_drink1'] ? 'Free Drink 1<br>' : '' ?>
                <?= $v['free_drink2'] ? 'Free Drink 2<br>' : '' ?>
                <?= $v['lapdance'] ? 'Lapdance<br>' : '' ?>
                <?= $v['photoshot'] ? 'Fotoshooting' : '' ?>
            </td>
            <td><?= $v['kw'] ?></td>
            <td><?= $v['erstellt_am'] ?></td>
            <td>
                <form method="post" style="display:inline;" onsubmit="return confirm('Wirklich löschen?');">
                    <input type="hidden" name="delete_id" value="<?= $v['id'] ?>">
                    <input type="submit" value="Löschen">
                </form>
                <form method="get" action="vip.php" style="display:inline;">
                    <input type="hidden" name="edit_id" value="<?= $v['id'] ?>">
                    <input type="submit" value="Bearbeiten">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
