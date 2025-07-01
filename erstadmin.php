<?php
include 'config.php';
include 'menu.php';

function safe_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prüfen, ob bereits ein Admin existiert
    $sql_check = "SELECT * FROM benutzer WHERE rolle = 'admin'";
    $stmt_check = $pdo->query($sql_check);

    if ($stmt_check->rowCount() > 0) {
        echo "Ein Admin existiert bereits. Diese Seite sollte nicht mehr verwendet werden.";
        exit;
    }

    // Admin hinzufügen
    $sql_insert = "INSERT INTO benutzer (username, passwort_hash, rolle) VALUES (?, ?, 'admin')";
    $stmt_insert = $pdo->prepare($sql_insert);
    $hashed_password = safe_password($password);

    if ($stmt_insert->execute([$username, $hashed_password])) {
        echo "Der erste Admin wurde erfolgreich hinzugefügt!";
    } else {
        echo "Fehler beim Hinzufügen des Admins.";
    }
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