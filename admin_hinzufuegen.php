<?php
include 'config.php';
include 'menu.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
	$role = $_POST['role'];

    // Überprüfen, ob der Benutzername oder die E-Mail bereits existiert
    $stmt_check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error = "Benutzername oder E-Mail bereits vergeben.";
    } else {
        // Passwort hashen und neuen Admin hinzufügen
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $username, $hashed_password, $email, $role);

        $success = $stmt_insert->execute() ? "Neuer User erfolgreich hinzugefügt." : "Fehler beim Hinzufügen des Admins.";
    }
}

// Benutzer abrufen
$result_users = $conn->query("SELECT id, username, email, role FROM users");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>User Verwaltung</title>
</head>
<body>

<br><h2>User hinzufügen</h2>

<?php if (isset($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if (isset($success)): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="post">
<table>
<tr>
    <td><label for="username">Benutzername:</label></td>
    <td><input type="text" id="username" name="username" required></td>
</tr>
<tr>
    <td><label for="email">E-Mail:</label></td>
    <td><input type="email" id="email" name="email" required></td>
</tr>
<tr>
	<td><label for="role">Rolle:</label></td>
	<td><select name="role">
        <option value="user">User</option>
        <option value="editor">Editor</option>
        <option value="admin">Admin</option>
        </select></td>
<tr>
    <td><label for="password">Kennwort:</label></td>
    <td><input type="password" id="password" name="password" required></td>
</tr>
<tr>
    <td colspan="2"><center><input type="submit" value="User hinzufügen"></center></td>
</tr>
</table>
</form>

<h2>Benutzerliste</h2>
<table border="0">
    <tr>
        <th>ID</th>
        <th>Benutzername</th>
        <th>E-Mail</th>
        <th>Rolle</th>
        <th>Aktionen</th>
    </tr>
    <?php while ($user = $result_users->fetch_assoc()): ?>
    <tr>
        <td><?php echo $user['id']; ?></td>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
		<?php $role = $user['role']; ?>
        <td><?php echo $rollenUebersetzung[$role] ; ?></td>
        <td>
            <a href="edit_user.php?id=<?php echo $user['id']; ?>">Bearbeiten</a> |
            <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Möchten Sie diesen Benutzer wirklich löschen?');">Löschen</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
