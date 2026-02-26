-- Creazione delle tabelle per GameUp
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    template_type VARCHAR(50) NOT NULL,
    content JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT,
    student_name VARCHAR(255) NOT NULL,
    score INT NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Inseriamo un quiz di test per verificare che tutto funzioni
INSERT INTO games (title, template_type, content) VALUES 
('Quiz Benvenuto', 'quiz', '{"questions": [{"q": "Il server Docker è attivo?", "a": "Sì", "options": ["Sì", "No", "Forse"]}]}');