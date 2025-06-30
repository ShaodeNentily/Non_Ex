<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();

// Aktuelle KW aus Config holen (max ID)
$stmt = $pdo->query("SELECT MAX(id) AS max_kw FROM Config");
$config = $stmt->fetch();
$current_kw_id = $config ? (int)$config['max_kw'] : 0;

// Mitarbeiter laden (Position Security oder alle, je nach Bedarf)
$mitarbeiter = $pdo->query("SELECT id, name FROM mitarbeiter ORDER BY name")->fetchAll();

// Positionen laden
$positionen = $pdo->query("SELECT * FROM security_position ORDER BY bezeichnung")->fetchAll();

// Stunden 0 bis 23
$stunden = range(0, 23);

// Einteilungen laden für aktuelle KW
$stmt = $pdo->prepare("SELECT * FROM security_einteilung WHERE kw_id = ?");
$stmt->execute([$current_kw_id]);
$einteilungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daten in Array für schnellen Zugriff
$data = [];
foreach ($einteilungen as $row) {
    $data[$row['mitarbeiter_id']][$row['stunde']] = [
        'aktiv' => $row['aktiv'],
        'position_id' => $row['position_id']
    ];
}

// Formular absenden und speichern
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($mitarbeiter as $m) {
        $mid = $m['id'];
        foreach ($stunden as $s) {
            $aktiv = isset($_POST["zeit_{$mid}_{$s}"]) ? 1 : 0;
            $posKey = "pos_{$mid}_{$s}";
            $position_id = isset($_POST[$posKey]) && $_POST[$posKey] !== '' ? (int)$_POST[$posKey] : null;

            // Existiert Eintrag?
            $stmt = $pdo->prepare("SELECT id FROM security_einteilung WHERE mitarbeiter_id = ? AND kw_id = ? AND stunde = ?");
            $stmt->execute([$mid, $current_kw_id, $s]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE security_einteilung SET aktiv = ?, position_id = ? WHERE id = ?");
                $stmt->execute([$aktiv, $position_id, $exists]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO security_einteilung (mitarbeiter_id, kw_id, stunde, aktiv, position_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$mid, $current_kw_id, $s, $aktiv, $position_id]);
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Security-Einteilung KW <?= $current_kw_id ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: center; }
        select, input[type="checkbox"] { width: 100%; }
        th.stunde { width: 40px; }
        td.position select { width: 100%; }
    </style>
</head>
<body>
    <h1>Security-Einteilung für KW <?= $current_kw_id ?></h1>

    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Mitarbeiter</th>
                    <?php foreach ($stunden as $s): ?>
                        <th class="stunde"><?= $s ?></th>
                        <th class="position">Pos.</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mitarbeiter as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <?php foreach ($stunden as $s): 
                            $checked = isset($data[$m['id']][$s]['aktiv']) && $data[$m['id']][$s]['aktiv'] ? 'checked' : '';
                            $selected_pos = $data[$m['id']][$s]['position_id'] ?? '';
                        ?>
                            <td>
                                <input type="checkbox" name="zeit_<?= $m['id'] ?>_<?= $s ?>" <?= $checked ?>>
                            </td>
                            <td class="position">
                                <select name="pos_<?= $m['id'] ?>_<?= $s ?>">
                                    <option value="">-</option>
                                    <?php foreach ($positionen as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $selected_pos) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['bezeichnung']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Speichern</button>
    </form>
</body>
</html>
