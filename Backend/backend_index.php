<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$conn = new mysqli(
    getenv('DB_HOST') ?: 'db',
    getenv('DB_USER') ?: 'gameup_user',
    getenv('DB_PASS') ?: 'gameup_pass',
    getenv('DB_NAME') ?: 'gameup'
);
if ($conn->connect_error) { http_response_code(500); echo json_encode(['error'=>'DB: '.$conn->connect_error]); exit(); }
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri    = preg_replace('#^/api#', '', $uri);

// ROUTER
if      ($method==='GET'  && $uri==='/games')                                         getGames($conn);
elseif  ($method==='GET'  && preg_match('#^/games/(\d+)$#',$uri,$m))                 getGame($conn,(int)$m[1]);
elseif  ($method==='POST' && $uri==='/games')                                         createGame($conn);
elseif  ($method==='POST' && preg_match('#^/games/(\d+)/play$#',$uri,$m))            incPlay($conn,(int)$m[1]);
elseif  ($method==='GET'  && preg_match('#^/games/(\d+)/scores$#',$uri,$m))          getScores($conn,(int)$m[1]);
elseif  ($method==='POST' && preg_match('#^/games/(\d+)/scores$#',$uri,$m))          submitScore($conn,(int)$m[1]);
elseif  ($method==='GET'  && $uri==='/forum/categories')                               forumCats($conn);
elseif  ($method==='GET'  && preg_match('#^/forum/categories/(\d+)/posts$#',$uri,$m)) forumPosts($conn,(int)$m[1]);
elseif  ($method==='POST' && preg_match('#^/forum/categories/(\d+)/posts$#',$uri,$m)) forumNewPost($conn,(int)$m[1]);
elseif  ($method==='GET'  && preg_match('#^/forum/posts/(\d+)$#',$uri,$m))            forumPost($conn,(int)$m[1]);
elseif  ($method==='POST' && preg_match('#^/forum/posts/(\d+)/replies$#',$uri,$m))    forumReply($conn,(int)$m[1]);
else    { http_response_code(404); echo json_encode(['error'=>'Not found','uri'=>$uri]); }

$conn->close();

// ── GAMES ──────────────────────────────────────────────────
function getGames($conn) {
    $subj = isset($_GET['subject']) ? trim($_GET['subject']) : null;
    if ($subj) {
        $s = $conn->prepare('SELECT id,title,description,subject,type,author,plays,created_at FROM games WHERE subject=? ORDER BY created_at DESC');
        $s->bind_param('s',$subj);
    } else {
        $s = $conn->prepare('SELECT id,title,description,subject,type,author,plays,created_at FROM games ORDER BY created_at DESC');
    }
    $s->execute(); $rows=[];
    $r=$s->get_result(); while($row=$r->fetch_assoc()) $rows[]=$row;
    echo json_encode($rows);
}

function getGame($conn,$id) {
    $s=$conn->prepare('SELECT id,title,description,subject,type,author,data,plays,created_at FROM games WHERE id=?');
    $s->bind_param('i',$id); $s->execute();
    $row=$s->get_result()->fetch_assoc();
    if (!$row) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
    $row['data']=json_decode($row['data'],true);
    echo json_encode($row);
}

function createGame($conn) {
    $b=json_decode(file_get_contents('php://input'),true);
    if (!$b) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); return; }
    foreach(['title','subject','type','data'] as $f)
        if (empty($b[$f])) { http_response_code(400); echo json_encode(['error'=>"Missing $f"]); return; }
    if (!in_array($b['type'],['flashcard','matching','quiz','snake','tetris']))
        { http_response_code(400); echo json_encode(['error'=>'Invalid type']); return; }
    $t=trim($b['title']); $d=trim($b['description']??''); $subj=trim($b['subject']);
    $type=$b['type']; $auth=trim($b['author']??'Anonimo'); $data=json_encode($b['data']);
    $s=$conn->prepare('INSERT INTO games (title,description,subject,type,author,data) VALUES (?,?,?,?,?,?)');
    $s->bind_param('ssssss',$t,$d,$subj,$type,$auth,$data);
    if ($s->execute()) { http_response_code(201); echo json_encode(['id'=>$conn->insert_id]); }
    else { http_response_code(500); echo json_encode(['error'=>'DB error']); }
}

function incPlay($conn,$id) {
    $s=$conn->prepare('UPDATE games SET plays=plays+1 WHERE id=?');
    $s->bind_param('i',$id); $s->execute(); echo json_encode(['ok'=>true]);
}

function getScores($conn,$gid) {
    $s=$conn->prepare('SELECT player_name,score,created_at FROM scores WHERE game_id=? ORDER BY score DESC LIMIT 10');
    $s->bind_param('i',$gid); $s->execute(); $rows=[];
    $r=$s->get_result(); while($row=$r->fetch_assoc()) $rows[]=$row;
    echo json_encode($rows);
}

function submitScore($conn,$gid) {
    $b=json_decode(file_get_contents('php://input'),true);
    $name=trim($b['player_name']??'Giocatore'); $score=(int)($b['score']??0);
    $chk=$conn->prepare('SELECT id FROM games WHERE id=?'); $chk->bind_param('i',$gid); $chk->execute();
    if ($chk->get_result()->num_rows===0) { http_response_code(404); echo json_encode(['error'=>'Game not found']); return; }
    $s=$conn->prepare('INSERT INTO scores (game_id,player_name,score) VALUES (?,?,?)');
    $s->bind_param('isi',$gid,$name,$score);
    if ($s->execute()) {
        $lb=$conn->prepare('SELECT player_name,score FROM scores WHERE game_id=? ORDER BY score DESC LIMIT 10');
        $lb->bind_param('i',$gid); $lb->execute(); $scores=[];
        $r=$lb->get_result(); while($row=$r->fetch_assoc()) $scores[]=$row;
        echo json_encode(['success'=>true,'leaderboard'=>$scores]);
    } else { http_response_code(500); echo json_encode(['error'=>'DB error']); }
}

// ── FORUM ──────────────────────────────────────────────────
function forumCats($conn) {
    $s=$conn->prepare('SELECT fc.*, (SELECT COUNT(*) FROM forum_posts fp WHERE fp.category_id=fc.id) as post_count, (SELECT MAX(created_at) FROM forum_posts fp2 WHERE fp2.category_id=fc.id) as last_post FROM forum_categories fc ORDER BY fc.id');
    $s->execute(); $rows=[];
    $r=$s->get_result(); while($row=$r->fetch_assoc()) $rows[]=$row;
    echo json_encode($rows);
}

function forumPosts($conn,$catId) {
    $s=$conn->prepare('SELECT id,author,title,reply_count,views,created_at FROM forum_posts WHERE category_id=? ORDER BY created_at DESC');
    $s->bind_param('i',$catId); $s->execute(); $rows=[];
    $r=$s->get_result(); while($row=$r->fetch_assoc()) $rows[]=$row;
    echo json_encode($rows);
}

function forumPost($conn,$id) {
    $conn->query("UPDATE forum_posts SET views=views+1 WHERE id=$id");
    $s=$conn->prepare('SELECT * FROM forum_posts WHERE id=?');
    $s->bind_param('i',$id); $s->execute();
    $post=$s->get_result()->fetch_assoc();
    if (!$post) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
    $rs=$conn->prepare('SELECT * FROM forum_replies WHERE post_id=? ORDER BY created_at ASC');
    $rs->bind_param('i',$id); $rs->execute(); $replies=[];
    $r=$rs->get_result(); while($row=$r->fetch_assoc()) $replies[]=$row;
    $post['replies']=$replies;
    echo json_encode($post);
}

function forumNewPost($conn,$catId) {
    $b=json_decode(file_get_contents('php://input'),true);
    $author=trim($b['author']??'Anonimo'); $title=trim($b['title']??''); $body=trim($b['body']??'');
    if (!$title||!$body) { http_response_code(400); echo json_encode(['error'=>'Missing fields']); return; }
    $s=$conn->prepare('INSERT INTO forum_posts (category_id,author,title,body) VALUES (?,?,?,?)');
    $s->bind_param('isss',$catId,$author,$title,$body);
    if ($s->execute()) { http_response_code(201); echo json_encode(['id'=>$conn->insert_id]); }
    else { http_response_code(500); echo json_encode(['error'=>'DB error']); }
}

function forumReply($conn,$postId) {
    $b=json_decode(file_get_contents('php://input'),true);
    $author=trim($b['author']??'Anonimo'); $body=trim($b['body']??'');
    if (!$body) { http_response_code(400); echo json_encode(['error'=>'Empty']); return; }
    $s=$conn->prepare('INSERT INTO forum_replies (post_id,author,body) VALUES (?,?,?)');
    $s->bind_param('iss',$postId,$author,$body);
    if ($s->execute()) {
        $conn->query("UPDATE forum_posts SET reply_count=reply_count+1 WHERE id=$postId");
        http_response_code(201); echo json_encode(['id'=>$conn->insert_id]);
    } else { http_response_code(500); echo json_encode(['error'=>'DB error']); }
}
