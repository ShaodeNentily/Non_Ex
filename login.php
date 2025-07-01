<?php
session_start();
include 'config.php';
include 'menu.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM benutzer WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $login_error = "Benutzer nicht gefunden.";
        } elseif (!isset($user['passwort_hash'])) {
            $login_error = "Fehler: Passwortfeld fehlt.";
        } elseif (password_verify($password, $user['passwort_hash'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['role'] = $user['rolle']; // Feldname in DB ist 'rolle'
            $_SESSION['username'] = $user['username'];
            header("Location: bar.php");
            exit;
        } else {
            $login_error = "Falsches Passwort.";
        }

    } elseif ($_POST['action'] == 'register') {
        $username = $_POST['username'];
        $password = passwort_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = $_POST['email'];
        $rolle = "user"; // Standardrolle

        $stmt = $pdo->prepare("SELECT * FROM benutzer WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $register_error = "Benutzername bereits vergeben.";
        } else {
            $sql = "INSERT INTO benutzer (username, passwort_hash, email, rolle) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$username, $password, $email, $rolle])) {
                $register_success = "Registrierung erfolgreich! Sie können sich jetzt einloggen.";
				$sql = "INSERT INTO mitarbeiter (name, position) VALUES (?, ?)";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([$username, 'Staff']);
            } else {
                $register_error = "Fehler bei der Registrierung. Bitte versuchen Sie es erneut.";
            }
        }

    } elseif ($_POST['action'] == 'reset_password') {
        $email = $_POST['email'];

        $stmt = $pdo->prepare("SELECT * FROM benutzer WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $sql = "UPDATE benutzer SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$token, $expiry, $email]);

            $reset_link = "http://srv-fs01:81/reset_password.php?token=" . $token;
            $subject = "Passwort zurücksetzen";
            $message = "Klicken Sie auf den folgenden Link, um Ihr Passwort zurückzusetzen:\n\n" . $reset_link;
            mail($email, $subject, $message);

            $reset_message = "Ein Link zum Zurücksetzen des Passworts wurde an Ihre E-Mail-Adresse gesendet.";
        } else {
            $reset_error = "Diese E-Mail-Adresse ist nicht registriert.";
        }
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

<!-- Login-Formular -->
<form method="post" action="">
    <h2>Login</h2>
    <input type="hidden" name="action" value="login">
    
    <label for="login_username">Benutzername:</label>
    <input type="text" id="login_username" name="username" required>
    
    <label for="login_password">Passwort:</label>
    <input type="password" id="login_password" name="password" required>
    
    <input type="submit" value="Einloggen">
</form>

<!-- Registrierungsformular -->
<form method="post" action="">
    <h2>Registrieren</h2>
    <input type="hidden" name="action" value="register">
    
    <label for="reg_username">Benutzername:</label>
    <input type="text" id="reg_username" name="username" required>
    
    <label for="reg_email">E-Mail:</label>
    <input type="email" id="reg_email" name="email" required>
    
    <label for="reg_password">Passwort:</label>
    <input type="password" id="reg_password" name="password" required>
    
    <input type="submit" value="Registrieren">
</form>

<!-- Passwort-Zurücksetzen-Formular -->
<form method="post" action="">
    <h2>Passwort zurücksetzen</h2>
    <input type="hidden" name="action" value="reset_password">
    
    <label for="reset_email">E-Mail:</label>
    <input type="email" id="reset_email" name="email" required>
    
    <input type="submit" value="Link zum Zurücksetzen senden">
</form>

</body>
</html>
