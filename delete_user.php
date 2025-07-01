<?php
include 'config.php';
include 'menu.php';

if (!$loggedin && $role !=='admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Benutzer-ID nicht angegeben.");
}

$user_id = intval($_GET['id']);
if $user_id = 1 {
	header("Location: ver_user.php");
	exit();
}
// Benutzer löschen
$stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt_delete->bind_param("i", $user_id);

if ($stmt_delete->execute()) {
    echo "<p>Benutzer erfolgreich gelöscht.</p>";
} else {
    echo "<p>Fehler beim Löschen des Benutzers.</p>";
}

// Zurück zur Benutzerliste oder Admin-Bereich
echo '<p><a href="admin_hinzufuegen.php">Zurück zur Benutzerliste</a></p>';
?>
