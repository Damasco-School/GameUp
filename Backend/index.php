<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Credenziali del database (le stesse del docker-compose)
$host = 'db';
$user = 'root';
$pass = 'root_password';
$db   = 'gameup_db';

// Connessione con MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Verifica connessione
if ($conn->connect_error) {
    die(json_encode(["error" => "Connessione fallita: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. GET: Recupera i quiz
if ($method == 'GET') {
    $sql = "SELECT * FROM games";
    $result = $conn->query($sql);
    $games = [];
    
    while($row = $result->fetch_assoc()) {
        $games[] = $row;
    }
    echo json_encode($games);
}

// 2. POST: Salva un nuovo quiz
if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $title = $conn->real_escape_string($data['title']);
    $template = $conn->real_escape_string($data['template_type']);
    $content = $conn->real_escape_string(json_encode($data['content']));

    $sql = "INSERT INTO games (title, template_type, content) VALUES ('$title', '$template', '$content')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Gioco salvato!", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

$conn->close();
?>