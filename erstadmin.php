<?php
// Einbindung der Konfigurationsdatei für die Datenbankverbindung
include 'config.php';
include 'menu.php';

// Funktion zur Passwortverschlüsselung
function safe_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Überprüfen, ob bereits ein Admin existiert
    $sql_check = "SELECT * FROM benutzer WHERE is_admin = 1";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "Ein Admin existiert bereits. Diese Seite sollte nicht mehr verwendet werden.";
        exit;
    }

    // Wenn kein Admin existiert, füge den neuen Admin hinzu
    $sql_insert = "INSERT INTO benutzer (username, passwort_hash, rolle) VALUES (?, ?, admin)";
    $stmt_insert = $conn->prepare($sql_insert);
    $hashed_password = safe_password($password); // Passwort verschlüsseln
    $stmt_insert->bind_param("ss", $username, $hashed_password);

    if ($stmt_insert->execute()) {
        echo "Der erste Admin wurde erfolgreich hinzugefügt!";
    } else {
        echo "Fehler beim Hinzufügen des Admins: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Erster Admin hinzufügen</title>
</head>
<body>

<h2>Ersten Admin hinzufügen</h2>

<form method="post" action="">
    <label for="username">Benutzername:</label>
    <input type="text" id="username" name="username" required><br><br>

    <label for="password">Kennwort:</label>
    <input type="password" id="password" name="password" required><br><br>

    <input type="submit" value="Admin hinzufügen">
</form>

</body>
</html>
