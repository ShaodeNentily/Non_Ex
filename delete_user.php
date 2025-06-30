<?php
include 'config.php';
include 'menu.php';

if (!isset($_GET['id'])) {
    die("Benutzer-ID nicht angegeben.");
}

$user_id = intval($_GET['id']);

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
