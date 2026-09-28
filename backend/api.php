<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'fit_quest_db';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Failed']);
    exit;
}

$action = $_GET['action'] ?? '';

// REGISTRATION
if ($action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = strtoupper(trim($data['username'] ?? ''));
    $age = intval($data['age'] ?? 0);

    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM player WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists! Please use Login.']);
        exit;
    }

    $now = round(microtime(true) * 1000);
    $stmt = $pdo->prepare("INSERT INTO player (username, age, lastRefresh, lastStaminaRegen) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $age, $now, $now]);
    $player_id = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO tower (player_id, towerReached) VALUES (?, 1)")->execute([$player_id]);

    $_SESSION['player_id'] = $player_id;
    echo json_encode(['success' => true]);
}

// LOGIN
elseif ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = strtoupper(trim($data['username'] ?? ''));

    $stmt = $pdo->prepare("SELECT id FROM player WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['player_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username not found! Please register.']);
    }
}

// LOGOUT
elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}

// LOAD DATA
elseif ($action === 'load') {
    if (!isset($_SESSION['player_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    $pid = $_SESSION['player_id'];

    $stmt = $pdo->prepare("SELECT * FROM player WHERE id = ?");
    $stmt->execute([$pid]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT towerReached FROM tower WHERE player_id = ?");
    $stmt->execute([$pid]);
    $tower = $stmt->fetch(PDO::FETCH_ASSOC);

    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT quest_id FROM quest WHERE player_id = ? AND date_completed = ?");
    $stmt->execute([$pid, $today]);
    $quests = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'data' =>[
            'name' => $player['username'],
            'age' => $player['age'],
            'lvl' => $player['lvl'],
            'xp' => $player['xp'],
            'hp' => $player['hp'],
            'atk' => $player['atk'],
            'arm' => $player['arm'],
            'pts' => $player['pts'],
            'stamina' => $player['stamina'],
            'height' => $player['height'],
            'weight' => $player['weight'],
            'lastRefresh' => $player['lastRefresh'],
            'lastStaminaRegen' => $player['lastStaminaRegen'],
            'towerReached' => $tower ? $tower['towerReached'] : 1,
            'completedQuests' => $quests
        ]
    ]);
}

// SAVE DATA
elseif ($action === 'save') {
    if (!isset($_SESSION['player_id'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    $pid = $_SESSION['player_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) exit;

    $stmt = $pdo->prepare("UPDATE player SET lvl=?, xp=?, hp=?, atk=?, arm=?, pts=?, stamina=?, height=?, weight=?, lastRefresh=?, lastStaminaRegen=? WHERE id=?");
    $stmt->execute([
        $data['lvl'], $data['xp'], $data['hp'], $data['atk'], $data['arm'], $data['pts'],
        $data['stamina'], $data['height'], $data['weight'], $data['lastRefresh'], $data['lastStaminaRegen'],
        $pid
    ]);

    $stmt = $pdo->prepare("UPDATE tower SET towerReached=? WHERE player_id=?");
    $stmt->execute([$data['towerReached'], $pid]);

    $today = date('Y-m-d');
    $existing = $pdo->prepare("SELECT quest_id FROM quest WHERE player_id = ? AND date_completed = ?");
    $existing->execute([$pid, $today]);
    $existing_quests = $existing->fetchAll(PDO::FETCH_COLUMN);

    $completed = $data['completedQuests'] ??[];
    $to_insert = array_diff($completed, $existing_quests);

    if (!empty($to_insert)) {
        $qStmt = $pdo->prepare("INSERT INTO quest (player_id, quest_id, date_completed) VALUES (?, ?, ?)");
        foreach($to_insert as $q) {
            $qStmt->execute([$pid, $q, $today]);
        }
    }

    echo json_encode(['success' => true]);
}
?>