<?php
session_start();
session_unset();     // Alle Session-Variablen löschen
session_destroy();   // Die Session selbst zerstören

// Optional: Session-Cookie löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Weiterleitung zur Login-Seite
header("Location: login.php");
exit;
?>