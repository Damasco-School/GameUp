CREATE DATABASE IF NOT EXISTS gameup;
USE gameup;

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(100) NOT NULL,
    type ENUM('flashcard','matching','quiz','snake','tetris') NOT NULL,
    author VARCHAR(100) DEFAULT 'Anonimo',
    data JSON NOT NULL,
    plays INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(100),
    icon VARCHAR(10) DEFAULT '💬',
    description TEXT,
    post_count INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS forum_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    author VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    reply_count INT DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE
);

INSERT INTO forum_categories (name, subject, icon, description) VALUES
('Generale',    NULL,         '🌍', 'Chat libera, annunci e discussioni generali'),
('Storia',      'storia',     '🏛️', 'Discussioni, dubbi e quiz di storia'),
('Matematica',  'matematica', '📐', 'Problemi, esercizi e aiuto matematica'),
('Scienze',     'scienze',    '🔬', 'Fisica, chimica, biologia'),
('Letteratura', 'letteratura','📚', 'Libri, autori, temi e analisi'),
('Geografia',   'geografia',  '🌍', 'Capitali, territori, cartografia'),
('Informatica', 'informatica','💻', 'Programmazione, reti, sistemi');

INSERT INTO games (title, description, subject, type, author, data) VALUES
('Capitali d''Europa','Collega ogni paese alla sua capitale','geografia','matching','Prof. Rossi',
 '{"pairs":[{"term":"Italia","definition":"Roma"},{"term":"Francia","definition":"Parigi"},{"term":"Germania","definition":"Berlino"},{"term":"Spagna","definition":"Madrid"},{"term":"Portogallo","definition":"Lisbona"},{"term":"Polonia","definition":"Varsavia"}]}'),
('Antica Roma — Quiz','Domande sull''Impero Romano','storia','quiz','Prof. Martini',
 '{"questions":[{"question":"In che anno cadde l''Impero Romano d''Occidente?","options":["476 d.C.","380 d.C.","753 a.C.","44 a.C."],"correct":0},{"question":"Chi fu il primo imperatore romano?","options":["Giulio Cesare","Augusto","Nerone","Traiano"],"correct":1},{"question":"Come si chiamava il Senato di Roma?","options":["Curia","Forum","Campus","Comitia"],"correct":0}]}'),
('Vocaboli Inglese','Flashcard per imparare l''inglese','letteratura','flashcard','Giulia R.',
 '{"cards":[{"front":"Apple","back":"Mela"},{"front":"House","back":"Casa"},{"front":"School","back":"Scuola"},{"front":"Friend","back":"Amico"},{"front":"Water","back":"Acqua"},{"front":"Book","back":"Libro"}]}'),
('Equazioni I Grado','Quiz su equazioni di primo grado','matematica','quiz','Prof. Russo',
 '{"questions":[{"question":"Risolvi: 2x + 4 = 10","options":["x = 2","x = 3","x = 7","x = 5"],"correct":1},{"question":"Risolvi: 3x - 6 = 9","options":["x = 5","x = 3","x = 1","x = 7"],"correct":0},{"question":"Risolvi: x/2 = 8","options":["x = 4","x = 16","x = 10","x = 6"],"correct":1}]}'),
('Elementi Chimici','Collega simbolo ed elemento','scienze','matching','Prof. Bianchi',
 '{"pairs":[{"term":"H","definition":"Idrogeno"},{"term":"O","definition":"Ossigeno"},{"term":"Fe","definition":"Ferro"},{"term":"Au","definition":"Oro"},{"term":"Na","definition":"Sodio"},{"term":"C","definition":"Carbonio"}]}');

INSERT INTO forum_posts (category_id, author, title, body) VALUES
(1,'Admin','Benvenuti in GameUp! 🎉','Ciao a tutti! Questo è il forum ufficiale di GameUp. Usatelo per fare domande, condividere giochi e aiutarvi a vicenda. Buono studio!'),
(2,'Prof. Martini','Consigli per studiare la storia','Per memorizzare le date storiche vi consiglio di usare le flashcard. Create un gioco e esercitatevi ogni giorno!'),
(3,'Sofia L.','Ho dubbi sulle equazioni di 2° grado','Qualcuno può spiegarmi come si usa la formula quadratica? Non riesco a capire il discriminante...');

INSERT INTO forum_replies (post_id, author, body) VALUES
(3,'Prof. Russo','Certo! La formula è: x = (-b ± √(b²-4ac)) / 2a. Il discriminante è b²-4ac: se positivo hai 2 soluzioni, se zero 1, se negativo nessuna reale.'),
(3,'Marco T.','Guarda il gioco "Equazioni I Grado" nell''edificio Matematica, ti aiuta a fare pratica!');
