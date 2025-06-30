<?php
require_once 'config.php';



// SQL-Befehle zum Erstellen der Tabellen
$sql = "
CREATE TABLE fehlerberichte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datum_erstellt DATETIME DEFAULT CURRENT_TIMESTAMP,
    datum_erste_meldung DATE,
    titel VARCHAR(255),
    melder_email VARCHAR(255),
    funktionsbeschreibung TEXT,
    fehlermeldung TEXT,
    bearbeiter VARCHAR(255),
    fehlerbehebung TEXT,
    erledigt_percent ENUM('0', '50', '100') DEFAULT '0',
    abgenommen TINYINT(1) NOT NULL DEFAULT 0;,
    prio ENUM('A', 'B', 'C') DEFAULT 'A',
    bemerkung TEXT,
    bemerkung_abas TEXT,
	screenshot VARCHAR(255) DEFAULT NULL
);

CREATE TABLE benutzer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzername VARCHAR(50) UNIQUE NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
    passwort_hash VARCHAR(255) NOT NULL,
    rolle ENUM('user', 'editor', 'admin') NOT NULL
);

CREATE TABLE erledigt_werte (
    wert INT PRIMARY KEY
);

INSERT INTO erledigt_werte (wert) VALUES (0), (50), (100);

CREATE TABLE prio_werte (
    wert CHAR(1) PRIMARY KEY
);

INSERT INTO prio_werte (wert) VALUES ('A'), ('B'), ('C');
";

// Ausführen
if ($conn->multi_query($sql)) {
    // Warten bis alle Queries durch sind
    do {
        $conn->next_result();
    } while ($conn->more_results());
    
    // Weiterleitung
    header("Location: erstadmin.php");
    exit;
} else {
    echo "Fehler beim Erstellen der Tabellen: " . $conn->error;
}
?>