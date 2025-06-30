<?php
//session_start();
include 'config.php'; // Verbindungsdetails zur Datenbank
include 'menu.php';

// Überprüfen, ob das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // SQL-Abfrage zur Authentifizierung des Benutzers
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Überprüfen, ob der Benutzer existiert und das Passwort korrekt ist
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
			$_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = "Login fehlgeschlagen oder keine Berechtigung.";
        }

        $stmt->close();
        $conn->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'register') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = $_POST['email'];

        // Überprüfen, ob der Benutzername bereits existiert
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $register_error = "Benutzername bereits vergeben.";
        } else {
            // Benutzer in der Datenbank speichern
            $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
			$role = "user";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $password, $email, $role);
            if ($stmt->execute()) {
                $register_success = "Registrierung erfolgreich! Sie können sich jetzt einloggen.";
            } else {
                $register_error = "Fehler bei der Registrierung. Bitte versuchen Sie es erneut.";
            }
        }

        $stmt->close();
        $conn->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        $email = $_POST['email'];

        // SQL-Abfrage zur Überprüfung, ob die E-Mail existiert
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Generiere einen zufälligen Token
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Speichere den Token und das Ablaufdatum in der Datenbank
            $sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            // Versende den Link per E-Mail
            $reset_link = "http://srv-fs01:81/reset_password.php?token=" . $token; // Ändere dies zu deiner Domain
            $subject = "Passwort zurücksetzen";
            $message = "Klicken Sie auf den folgenden Link, um Ihr Passwort zurückzusetzen: " . $reset_link;
            mail($email, $subject, $message);

            $reset_message = "Ein Link zum Zurücksetzen des Passworts wurde an Ihre E-Mail-Adresse gesendet.";
        } else {
            $reset_error = "Diese E-Mail-Adresse ist nicht registriert.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

<h2>Benutzer Login</h2>

<?php if (isset($login_error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($login_error); ?></p>
<?php endif; ?>
<table>
<form method="post" action="">
	<tr>
		<input type="hidden" name="action" value="login">
		<td><label for="username">Benutzername:</label></td>
		<td><input type="text" id="username" name="username" required></td>
	</tr>
	<tr>
		<td><label for="password">Kennwort:</label></td>
		<td><input type="password" id="password" name="password" required></td>
	</tr>
	<tr>
		<td colspan="2"><center><br><input type="submit" value="Login"></center></td>
	</tr>
</form>
</table>
<h2>Registrieren</h2>

<?php if (isset($register_error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($register_error); ?></p>
<?php endif; ?>

<?php if (isset($register_success)): ?>
    <p style="color:green;"><?php echo htmlspecialchars($register_success); ?></p>
<?php endif; ?>

<table>
<form method="post" action="">
	<tr>
		<input type="hidden" name="action" value="register">
		<td><label for="username">Benutzername:</label></td>
		<td><input type="text" id="username" name="username" required></td>
	</tr>
	<tr>
		<td><label for="password">Kennwort:</label></td>
		<td><input type="password" id="password" name="password" required></td>
	</tr>
	<tr>
		<td><label for="email">E-Mail-Adresse:</label></td>
		<td><input type="email" id="email" name="email" required></td>
	</tr>
	<tr>
		<td colspan="2"><br><center><input type="submit" value="Registrieren"></center></td>
	</tr>
</form>
</table>

<h2>Passwort vergessen?</h2>
<table>
<form method="post" action="">
	<tr>
		<input type="hidden" name="action" value="reset_password">
		<td><label for="email">E-Mail-Adresse:</label></td>
		<td><input type="email" id="email" name="email" required></td>
	<tr>
	</tr>
		<td colspan="2"><br><center><input type="submit" value="Passwort zurücksetzen"></center></td>
	</tr>
</form>
</table>
<?php if (isset($reset_message)): ?>
    <p style="color:green;"><?php echo htmlspecialchars($reset_message); ?></p>
<?php endif; ?>

<?php if (isset($reset_error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($reset_error); ?></p>
<?php endif; ?>

</body>
</html>
