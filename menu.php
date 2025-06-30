<!DOCTYPE html>
<?
include 'config.php';
?>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css"> <!-- CSS-Datei hier einbinden -->
	<!---<script>
    // Dark-Mode umschalten und in localStorage speichern
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const tables = document.querySelectorAll('table');
        tables.forEach(table => table.classList.toggle('dark-mode'));

        // Zustand speichern
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    }

    // Dark-Mode beim Laden aktivieren, falls gespeichert
    window.onload = function() {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            const tables = document.querySelectorAll('table');
            tables.forEach(table => table.classList.add('dark-mode'));
        }
    }
</script> --->
<script>
    // Dark-Mode umschalten und in localStorage speichern
    function toggleDarkMode() {
        const body = document.body;
        const tables = document.querySelectorAll('table');
        const button = document.getElementById('darkModeButton');

        body.classList.toggle('dark-mode');
        tables.forEach(table => table.classList.toggle('dark-mode'));

        // Zustand speichern
        const darkModeEnabled = body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', darkModeEnabled ? 'enabled' : 'disabled');

        // Button-Text aktualisieren
        button.textContent = darkModeEnabled ? '☀️' : '🌙';
    }

    // Dark-Mode beim Laden aktivieren, falls gespeichert
    window.onload = function() {
        const darkModeButton = document.getElementById('darkModeButton');
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            const tables = document.querySelectorAll('table');
            tables.forEach(table => table.classList.add('dark-mode'));
            darkModeButton.textContent = '☀️'; // Button-Text für aktiven Dark-Mode
        }
    }
	window.onload = function () {
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const storedDarkMode = localStorage.getItem('darkMode');

    if (storedDarkMode === 'enabled' || (storedDarkMode === null && prefersDarkMode)) {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeText').textContent = '☀';
    }
};
</script>
</head>
<body>
    <?php
	session_set_cookie_params(0);
    session_start();
    $loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'unbekannt';
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unbekannt';
	$rollenUebersetzung = [
    'admin' => 'Administrator',
    'editor' => 'Bearbeiter',
    'user' => 'Benutzer'
];
?>
    
    
    <!-- Allgemeines Menü -->
    <nav>
        <a href="index.php">Übersicht Projekte</a>
        <?php if ($loggedin): ?> <a href="hinzufuegen_projekt.php">Projekt hinzufügen</a><?php endif; ?>
        <?php if (!$loggedin): ?><a href="Admin.php">Benutzer Login</a><?php endif; ?>
		<?php if ($loggedin): ?><a href="logout.php">Logout</a><?php endif; ?>
		<button onclick="toggleDarkMode()" id="darkModeButton">🌙</button>
    </nav>

    <?php if ($loggedin): ?>
    <!-- Menü für alle eingeloggten Benutzer -->
    <nav>
        <a href="dashboard.php">Dashboard</a> <!-- Für alle Benutzer sichtbar -->
        <a href="kennwort_aendern.php">Kennwort ändern</a> <!-- Für alle Benutzer sichtbar -->
        
        <?php if ($role === 'editor' || $role === 'admin'): ?>
            <a href="bearbeiten_projekte.php">Projekte bearbeiten</a> <!-- Für Editor und Admin sichtbar -->
        <?php endif; ?>
        
        <?php if ($role === 'admin'): ?>
            <a href="combobox_hinzufuegen.php">Combobox-Werte hinzufügen</a> <!-- Nur für Admin sichtbar -->
            <a href="admin_hinzufuegen.php">Userverwaltung</a> <!-- Nur für Admin sichtbar -->
        <?php endif; ?>
        
        <p>
            Eingeloggt als: 
            <strong><font color="red"><?php echo htmlspecialchars($username); ?></font></strong>  Berechtigung:
			<?php if ($role === 'user'): ?>
				<strong><font color="white"><?php echo htmlspecialchars($role); ?> </font></strong>
			<?php endif; ?>
			<?php if ($role === 'editor'): ?>
				<strong><font color="green"><?php echo htmlspecialchars($role); ?> </font></strong>
			<?php endif; ?>
			<?php if ($role === 'admin'): ?>
				<strong><font color="red"><?php echo $rollenUebersetzung[$role] ?? 'Unbekannt'; ?> </font></strong>
			<?php endif; ?>
        </p>
    </nav>
<?php endif; ?>
</body>
</html>