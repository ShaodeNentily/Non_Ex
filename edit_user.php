<?php
include 'config.php';
include 'menu.php';
if (!$loggedin) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Benutzer-ID nicht angegeben.");
}

$user_id = intval($_GET['id']);
$user_query = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();

if ($user_result->num_rows === 0) {
    die("Benutzer nicht gefunden.");
}

$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
	$role = $_POST['role'];
    $new_password = $_POST['password'];

    $conn->begin_transaction();

    try {
        // Update der Benutzerdaten
        $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt_update->bind_param("sssi", $username, $email, $role, $user_id);
        $stmt_update->execute();

        // Kennwortänderung, falls ein neues Kennwort eingegeben wurde
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_password->bind_param("si", $hashed_password, $user_id);
            $stmt_password->execute();
        }

        $conn->commit();
        $success = "Benutzer erfolgreich aktualisiert.";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Fehler beim Aktualisieren des Benutzers: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Benutzer bearbeiten</title>
</head>
<body>
    <div class="form-container">
        <h2>Benutzer bearbeiten</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post">
            <table>
                <tr>
                    <th>Feld</th>
                    <th>Wert</th>
                </tr>
                <tr>
                    <td><label for="username">Benutzername:</label></td>
                    <td>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td><label for="email">E-Mail:</label></td>
                    <td>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td><label for="is_admin">Rolle:</label></td>
                    <td>
						<?php if ($user['id'] != 1): ?>
							<select name="role">
								<option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
								<option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
								<option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
							</select>
						<?php else: ?>
						<strong><?php echo ucfirst($user['role']); ?></strong> (festgelegt)
							<input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
							<?php endif; ?>
					</td>
                </tr>
                <tr>
                    <td><label for="password">Neues Kennwort (optional):</label></td>
                    <td>
                        <input type="password" id="password" name="password" placeholder="Neues Kennwort eingeben">
                    </td>
                </tr>
            </table><br><br>
            <input type="submit" value="Änderungen speichern">
        </form>
    </div>
</body>
</html>
</html>
