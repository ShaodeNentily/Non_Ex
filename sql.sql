CREATE TABLE mitarbeiter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL
);

CREATE TABLE getraenke (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    preis INT NOT NULL
);

INSERT INTO getraenke (name, preis) VALUES
('Milk','500'),
('Juice','500'),
('Soft Drink','500'),
('Cookie 3x','750'),
('Pizza Stice','1500'),
('Beer','1500'),
('Wodka','2000'),
('Whiskey','2000'),
('Rum','2000'),
('Jägermeister','2000'),
('Magerita','4000'),
('Tequila Sunrise','4000'),
('Pina Colada','4000'),
('Cherry Colada','4000'),
('Mojito','4000'),
('Long Island Icetea','400'),
('House Special','5000');

CREATE TABLE position (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO position (name) VALUES 
('Owner'),
('Co-Owner'),
('Manager'),
('Techniker'),
('Dancer'),
('Photographer'),
('Showdancer'),
('Security'),
('Bar'),
('Shouter'),
('Gambler'),
('Staff');

CREATE TABLE config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(255) NOT NULL
);

CREATE TABLE dancer_service (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service VARCHAR(255) NOT NULL,
    preis INT NOT NULL
);

INSERT INTO dancer_service (service, preis) VALUES
('-','0'),
('Dancefloor Companion','70000'),
('Date Experoence', '150000'),
('RP Guide 101','350000'),
('RP Guide 101 extension','150000'),
('More Private Dance','175000'),
('Hot Session 30min','300000'),
('Hot Session 60min','500000'),
('ERP Guide 101','350000'),
('ERP Guide 101 extension','150000');

CREATE TABLE dancer_service_zu_kosten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(255) NOT NULL,
    preis INT NOT NULL
);

CREATE TABLE counter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(255) NOT NULL,
    counter INT NOT NULL
);

INSERT INTO counter (bezeichnung, counter) VALUES
('0','0'),
('1','1'),
('2','2'),
('3','3'),
('4','4'),
('5','5');

CREATE TABLE rooms (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	bezeichnung VARCHAR(255) NOT NULL
	);

CREATE TABLE dancer_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunde VARCHAR(255) NOT NULL,
    service_id INT NOT NULL,
    anzahl INT NOT NULL,
    mitarbeiter_id INT NOT NULL,
    zusatz_gaeste_id INT NOT NULL,
    zusatz_kosten_id INT NOT NULL,
    KW_id INT NOT NULL,
    startzeit DATETIME NOT NULL,
    endzeit DATETIME NOT NULL,
    eingetragen_von VARCHAR(255) NOT NULL,
	FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (service_id) REFERENCES dancer_service(id),
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id),
    FOREIGN KEY (zusatz_gaeste_id) REFERENCES gast_counter(id),
    FOREIGN KEY (zusatz_kosten_id) REFERENCES dancer_service_zu_kosten(id)
);

CREATE TABLE verkaeufe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunde VARCHAR(255) NOT NULL,
    getraenk_id INT NOT NULL,
    menge INT NOT NULL,
	trinkgeld INT NOT NULL,
    mitarbeiter_id INT NOT NULL,
    KW_id INT NOT NULL,
    datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    eingetragen_von VARCHAR(255) NOT NULL,
    FOREIGN KEY (getraenk_id) REFERENCES getraenke(id),
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id)
);

CREATE TABLE photo_service_art (
    id INT AUTO_INCREMENT PRIMARY KEY,
    art VARCHAR(255) NOT NULL,
    preis INT NOT NULL
);

CREATE TABLE photo_service_zu_kosten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(255) NOT NULL,
    preis INT NOT NULL
);

CREATE TABLE photoshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunde VARCHAR(255) NOT NULL,
    vip BOOL DEFAULT FALSE,
    art_id INT NOT NULL,
    zusatz_gast_id INT NOT NULL,
    zusatz_kosten_id INT NOT NULL,
    mitarbeiter_id INT NOT NULL,
    KW_id INT NOT NULL,
    datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    eingetragen_von VARCHAR(255) NOT NULL,
    FOREIGN KEY (art_id) REFERENCES photo_service_art(id),
    FOREIGN KEY (zusatz_gast_id) REFERENCES gast_counter(id),
    FOREIGN KEY (zusatz_kosten_id) REFERENCES photo_service_zu_kosten(id),
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id)
);

CREATE TABLE security_position (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(255) NOT NULL
);

INSERT INTO security_position (Bezeichnung) VALUES
('Reception'),
('Private'),
('Danceflor'),
('VIP');


CREATE TABLE security_einteilung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mitarbeiter_id INT NOT NULL,
    kw_id INT NOT NULL,
    stunde TIME NOT NULL,
    aktiv BOOLEAN DEFAULT FALSE,
    position_id INT DEFAULT NULL,
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id),
    FOREIGN KEY (position_id) REFERENCES security_position(id)
);

CREATE TABLE benutzer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzername VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    passwort_hash VARCHAR(255) NOT NULL,
    rolle ENUM('user', 'editor', 'admin') NOT NULL
);

CREATE TABLE VIP_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wert VARCHAR(255) NOT NULL,
    typ ENUM('tier', 'preis') NOT NULL
);

INSERT INTO VIP_config (wert, typ) VALUES 
('Singel VIP', 'tier'),
('Double VIP', 'tier'),
('Gruppen VIP', 'tier'),
('Staff +1', 'tier'),
('0','preis'),
('500000', 'preis'),
('700000', 'preis'),
('1300000', 'preis');

CREATE TABLE VIP (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    tier_id INT,
    line_skip BOOLEAN DEFAULT 0,
    free_drink1 BOOLEAN DEFAULT 0,
    free_drink2 BOOLEAN DEFAULT 0,
    lapdance BOOLEAN DEFAULT 0,
    photoshot BOOLEAN DEFAULT 0,
    preis_id INT,
    mitarbeiter_id INT,
    kw INT,
    eingetragen_von VARCHAR(255) NOT NULL,
    erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tier_id) REFERENCES VIP_config(id),
    FOREIGN KEY (preis_id) REFERENCES VIP_config(id),
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id)
);
