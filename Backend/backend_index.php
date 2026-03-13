<?php
// ============================================================
//  GameUp Backend — index.php
//  Apache + PHP + MySQLi
// ============================================================

// CORS: allow frontend container to talk to backend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -------- DB Connection --------
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'gameup_user';
$pass = getenv('DB_PASS') ?: 'gameup_pass';
$name = getenv('DB_NAME') ?: 'gameup';

$conn = new mysqli($host, $user, $pass, $name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
    exit();
}
$conn->set_charset('utf8mb4');

// -------- Router --------
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');

// Strip leading /api if present
$uri = preg_replace('#^/api#', '', $uri);

// Routes:
//   GET  /games              → list all (optional ?subject=)
//   GET  /games/{id}         → single game
//   POST /games              → create game
//   POST /games/{id}/play    → increment play count
//   GET  /games/{id}/scores  → leaderboard
//   POST /games/{id}/scores  → submit score

if ($method === 'GET' && preg_match('#^/games$#', $uri)) {
    getGames($conn);

} elseif ($method === 'GET' && preg_match('#^/games/(\d+)$#', $uri, $m)) {
    getGame($conn, (int)$m[1]);

} elseif ($method === 'POST' && preg_match('#^/games$#', $uri)) {
    createGame($conn);

} elseif ($method === 'POST' && preg_match('#^/games/(\d+)/play$#', $uri, $m)) {
    incrementPlay($conn, (int)$m[1]);

} elseif ($method === 'GET' && preg_match('#^/games/(\d+)/scores$#', $uri, $m)) {
    getScores($conn, (int)$m[1]);

} elseif ($method === 'POST' && preg_match('#^/games/(\d+)/scores$#', $uri, $m)) {
    submitScore($conn, (int)$m[1]);

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found', 'uri' => $uri, 'method' => $method]);
}

$conn->close();

// ============================================================
//  HANDLERS
// ============================================================

function getGames($conn) {
    $subject = isset($_GET['subject']) ? trim($_GET['subject']) : null;

    if ($subject) {
        $stmt = $conn->prepare(
            'SELECT id, title, description, subject, type, author, plays, created_at
             FROM games WHERE subject = ? ORDER BY created_at DESC'
        );
        $stmt->bind_param('s', $subject);
    } else {
        $stmt = $conn->prepare(
            'SELECT id, title, description, subject, type, author, plays, created_at
             FROM games ORDER BY created_at DESC'
        );
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $games = [];
    while ($row = $result->fetch_assoc()) $games[] = $row;
    echo json_encode($games);
}

function getGame($conn, $id) {
    $stmt = $conn->prepare(
        'SELECT id, title, description, subject, type, author, data, plays, created_at
         FROM games WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Game not found']);
        return;
    }

    // Parse JSON data field
    $row['data'] = json_decode($row['data'], true);
    echo json_encode($row);
}

function createGame($conn) {
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    $required = ['title', 'subject', 'type', 'data'];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }

    $validTypes = ['flashcard', 'matching', 'quiz', 'snake', 'tetris'];
    if (!in_array($body['type'], $validTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid game type']);
        return;
    }

    $title       = trim($body['title']);
    $description = trim($body['description'] ?? '');
    $subject     = trim($body['subject']);
    $type        = $body['type'];
    $author      = trim($body['author'] ?? 'Anonimo');
    $data        = json_encode($body['data']);

    $stmt = $conn->prepare(
        'INSERT INTO games (title, description, subject, type, author, data)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssss', $title, $description, $subject, $type, $author, $data);

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Game created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create game']);
    }
}

function incrementPlay($conn, $id) {
    $stmt = $conn->prepare('UPDATE games SET plays = plays + 1 WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
}

function getScores($conn, $gameId) {
    $stmt = $conn->prepare(
        'SELECT player_name, score, created_at FROM scores
         WHERE game_id = ? ORDER BY score DESC LIMIT 10'
    );
    $stmt->bind_param('i', $gameId);
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = [];
    while ($row = $result->fetch_assoc()) $scores[] = $row;
    echo json_encode($scores);
}

function submitScore($conn, $gameId) {
    $body = json_decode(file_get_contents('php://input'), true);

    $playerName = trim($body['player_name'] ?? 'Giocatore');
    $score      = (int)($body['score'] ?? 0);

    // Verify game exists
    $check = $conn->prepare('SELECT id FROM games WHERE id = ?');
    $check->bind_param('i', $gameId);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Game not found']);
        return;
    }

    $stmt = $conn->prepare(
        'INSERT INTO scores (game_id, player_name, score) VALUES (?, ?, ?)'
    );
    $stmt->bind_param('isi', $gameId, $playerName, $score);

    if ($stmt->execute()) {
        // Return updated leaderboard
        $lb = $conn->prepare(
            'SELECT player_name, score FROM scores
             WHERE game_id = ? ORDER BY score DESC LIMIT 10'
        );
        $lb->bind_param('i', $gameId);
        $lb->execute();
        $result = $lb->get_result();
        $scores = [];
        while ($row = $result->fetch_assoc()) $scores[] = $row;

        echo json_encode(['success' => true, 'leaderboard' => $scores]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save score']);
    }
}
