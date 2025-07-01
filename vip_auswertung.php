<?php
include 'config.php';
include 'menu.php';

if (!$loggedin) {
    header("Location: login.php");
    exit();
}

$sql = "
SELECT v.*, 
    t.wert AS tier,
    p.wert AS preis,
    m.name AS mitarbeiter
FROM VIP v
LEFT JOIN VIP_config t ON v.tier_id = t.id
LEFT JOIN VIP_config p ON v.preis_id = p.id
LEFT JOIN mitarbeiter m ON v.mitarbeiter_id = m.id
ORDER BY v.kw DESC, v.erstellt_am DESC
";
$eintraege = $pdo->query($sql)->fetchAll();
?>

<h2>VIP-Auswertung</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>Tier</th>
        <th>Extras</th>
        <th>Preis</th>
        <th>Mitarbeiter</th>
        <th>KW</th>
        <th>Datum</th>
		<th>Eingetragen von</th>
    </tr>
    <?php foreach ($eintraege as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['name']) ?></td>
            <td><?= $v['tier'] ?></td>
            <td>
                <?= $v['line_skip'] ? 'Line Skip<br>' : '' ?>
                <?= $v['free_drink1'] ? 'Free Drink 1<br>' : '' ?>
                <?= $v['free_drink2'] ? 'Free Drink 2<br>' : '' ?>
                <?= $v['lapdance'] ? 'Lapdance<br>' : '' ?>
                <?= $v['photoshot'] ? 'Fotoshooting' : '' ?>
            </td>
            <td><?= $v['preis'] ?></td>
            <td><?= $v['mitarbeiter'] ?></td>
            <td><?= $v['kw'] ?></td>
            <td><?= $v['erstellt_am'] ?></td>
			<td><?= htmlspecialchars($v['eingetragen_von']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>