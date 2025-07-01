<?php
require_once 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();
}

// Alle KWs holen
$alleKWs = $pdo->query("SELECT id, bezeichnung FROM config ORDER BY id DESC")->fetchAll();

// Aktuelle KW (höchste ID) oder vom User gewählte KW
$aktuelleKW = isset($_GET['kw']) && ctype_digit($_GET['kw']) ? (int)$_GET['kw'] : $alleKWs[0]['id'];
$aktuelleKWBezeichnung = '';
foreach ($alleKWs as $kw) {
    if ($kw['id'] == $aktuelleKW) {
        $aktuelleKWBezeichnung = $kw['bezeichnung'];
        break;
    }
}

// Mitarbeiter laden (nur Security)
$sql = "
    SELECT m.*
    FROM mitarbeiter m
    JOIN Position p ON m.position = p.id
    WHERE p.name = ?
";
$mitarbeiter = $pdo->prepare($sql);
$mitarbeiter->execute(['Security']);
$mitarbeiter = $mitarbeiter->fetchAll();

// Positionen laden
$positionen = $pdo->query("SELECT * FROM security_position ORDER BY bezeichnung")->fetchAll();

// Stundenbereich
$stunden = range(19, 23);

// Einteilungen für gewählte KW laden
$stmt = $pdo->prepare("SELECT * FROM security_einteilung WHERE kw_id = ?");
$stmt->execute([$aktuelleKW]);
$einteilungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Strukturieren für Zugriff
$data = [];
foreach ($einteilungen as $row) {
    $data[$row['mitarbeiter_id']][$row['stunde']] = [
        'aktiv' => $row['aktiv'],
        'position_id' => $row['position_id']
    ];
}

// Speichern
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($mitarbeiter as $m) {
        $mid = $m['id'];
        foreach ($stunden as $s) {
            $aktiv = isset($_POST["zeit_{$mid}_{$s}"]) ? 1 : 0;
            $posKey = "pos_{$mid}_{$s}";
            $position_id = isset($_POST[$posKey]) && $_POST[$posKey] !== '' ? (int)$_POST[$posKey] : null;

            $stmt = $pdo->prepare("SELECT id FROM security_einteilung WHERE mitarbeiter_id = ? AND kw_id = ? AND stunde = ?");
            $stmt->execute([$mid, $aktuelleKW, $s]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE security_einteilung SET position_id = ? WHERE id = ?");
                $stmt->execute([$position_id, $exists]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO security_einteilung (mitarbeiter_id, kw_id, stunde, position_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$mid, $aktuelleKW, $s, $position_id]);
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?kw=" . $aktuelleKW);
    exit;
}
?>

<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Security-Einteilung – <?= htmlspecialchars($aktuelleKWBezeichnung) ?></title>
</head>
<body>
    <h1>Security-Einteilung – <?= htmlspecialchars($aktuelleKWBezeichnung) ?></h1>

    <form method="get" style="margin-bottom: 1em;">
        <label for="kw">Kalenderwoche wählen:</label>
        <select name="kw" id="kw" onchange="this.form.submit()">
            <?php foreach ($alleKWs as $kw): ?>
                <option value="<?= $kw['id'] ?>" <?= $kw['id'] == $aktuelleKW ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kw['bezeichnung']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Mitarbeiter</th>
                    <?php foreach ($stunden as $s): ?>
                        <th><?= $s ?>:00</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mitarbeiter as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <?php foreach ($stunden as $s): 
                            $selected_pos = $data[$m['id']][$s]['position_id'] ?? '';
                        ?>
                            <td>
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

    <h2>📊 Auswertung – <?= htmlspecialchars($aktuelleKWBezeichnung) ?></h2>
    <table>
        <thead>
            <tr>
                <th>Stunde</th>
                <th>Position</th>
                <th>Anzahl</th>
                <th>Mitarbeiter</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stunden as $s): ?>
                <?php
                    $sql = "
                        SELECT e.*, p.bezeichnung AS position_name, m.name AS mitarbeiter_name
                        FROM security_einteilung e
                        LEFT JOIN security_position p ON e.position_id = p.id
                        LEFT JOIN mitarbeiter m ON e.mitarbeiter_id = m.id
                        WHERE e.kw_id = ? AND e.stunde = ?
                        ORDER BY p.bezeichnung, m.name
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$aktuelleKW, $s]);
                    $results = $stmt->fetchAll();

                    $gruppen = [];
                    foreach ($results as $r) {
                        $pos = $r['position_name'] ?? '(keine)';
                        $gruppen[$pos][] = $r['mitarbeiter_name'];
                    }
                ?>

                <?php foreach ($gruppen as $pos => $namen): ?>
                    <tr>
                        <td><?= $s ?>:00</td>
                        <td><?= htmlspecialchars($pos) ?></td>
                        <td><?= count($namen) ?></td>
                        <td><?= implode(', ', array_map('htmlspecialchars', $namen)) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($gruppen)): ?>
                    <tr>
                        <td><?= $s ?>:00</td>
                        <td colspan="3"><em>Keine Eintragungen</em></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>