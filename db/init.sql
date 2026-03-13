-- GameUp Database Schema
CREATE DATABASE IF NOT EXISTS gameup;
USE gameup;

-- Games table: stores all user-created games
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

-- Scores table: leaderboard per game
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Sample games
INSERT INTO games (title, description, subject, type, author, data) VALUES
('Capitali d''Europa', 'Collega ogni paese alla sua capitale', 'geografia', 'matching', 'Prof. Rossi',
 '{"pairs":[{"term":"Italia","definition":"Roma"},{"term":"Francia","definition":"Parigi"},{"term":"Germania","definition":"Berlino"},{"term":"Spagna","definition":"Madrid"},{"term":"Portogallo","definition":"Lisbona"},{"term":"Polonia","definition":"Varsavia"}]}'),

('Antica Roma — Quiz', 'Domande sull''Impero Romano', 'storia', 'quiz', 'Prof. Martini',
 '{"questions":[{"question":"In che anno cadde l''Impero Romano d''Occidente?","options":["476 d.C.","380 d.C.","753 a.C.","44 a.C."],"correct":0},{"question":"Chi fu il primo imperatore romano?","options":["Giulio Cesare","Augusto","Nerone","Traiano"],"correct":1},{"question":"Come si chiamava il Senato di Roma?","options":["Curia","Forum","Campus","Comitia"],"correct":0}]}'),

('Vocaboli Inglese — Base', 'Flashcard per imparare l''inglese', 'letteratura', 'flashcard', 'Giulia R.',
 '{"cards":[{"front":"Apple","back":"Mela"},{"front":"House","back":"Casa"},{"front":"School","back":"Scuola"},{"front":"Friend","back":"Amico"},{"front":"Water","back":"Acqua"},{"front":"Book","back":"Libro"}]}'),

('Equazioni I Grado', 'Quiz su equazioni di primo grado', 'matematica', 'quiz', 'Prof. Russo',
 '{"questions":[{"question":"Risolvi: 2x + 4 = 10","options":["x = 2","x = 3","x = 7","x = 5"],"correct":1},{"question":"Risolvi: 3x - 6 = 9","options":["x = 5","x = 3","x = 1","x = 7"],"correct":0},{"question":"Risolvi: x/2 = 8","options":["x = 4","x = 16","x = 10","x = 6"],"correct":1}]}'),

('Elementi Chimici', 'Collega simbolo ed elemento', 'scienze', 'matching', 'Prof. Bianchi',
 '{"pairs":[{"term":"H","definition":"Idrogeno"},{"term":"O","definition":"Ossigeno"},{"term":"Fe","definition":"Ferro"},{"term":"Au","definition":"Oro"},{"term":"Na","definition":"Sodio"},{"term":"C","definition":"Carbonio"}]}');
