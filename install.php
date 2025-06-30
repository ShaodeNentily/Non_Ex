<?php
include 'menu.php';
if (file_exists('config.php')) {
    header("Location: index.php");
    exit;
}

$error = '';

// Wenn Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = $_POST['servername'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $dbname     = $_POST['dbname'];

    // Verbindung testen
    $conn = @new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $error = "Verbindung fehlgeschlagen: " . $conn->connect_error;
    } else {
        // Verbindung erfolgreich → config.php schreiben
        $configContent = <<<PHP
<?php
\$servername = "$servername";
\$username = "$username";
\$password = "$password";
\$dbname = "$dbname";

// Datenbankverbindung herstellen
\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);

// Verbindung prüfen
if (\$conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . \$conn->connect_error);
}
?>
PHP;

    file_put_contents('config.php', $configContent);
        $conn->close();

        // Datei selbst deaktivieren
        rename(__FILE__, 'install.locked.php');

        // Weiter zur Tabellenerstellung
        header("Location: install_tables.php");
        exit;
    }
}
?>
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Installation – Datenbank</title>
</head>
<body>
    <h1>Datenbankverbindung einrichten</h1>
    <form method="post">
        <table>
            <tr>
                <td><label for="servername">Servername:</label></td>
                <td><input type="text" id="servername" name="servername" value="localhost" required></td>
            </tr>
            <tr>
                <td><label for="username">Benutzername:</label></td>
                <td><input type="text" id="username" name="username" value="root" required></td>
            </tr>
            <tr>
                <td><label for="password">Passwort:</label></td>
                <td><input type="password" id="password" name="password"></td>
            </tr>
            <tr>
                <td><label for="dbname">Datenbankname:</label></td>
                <td><input type="text" id="dbname" name="dbname" value="update_db" required></td>
            </tr>
            <tr>
                <td colspan="2" class="button-row">
                    <button type="submit">Weiter</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>