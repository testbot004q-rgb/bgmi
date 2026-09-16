<?php
// index.php - Authentication Server for Game.Loader

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => false, 'reason' => 'POST request required'], 405);
}

$game    = isset($_POST['game'])     ? trim((string) $_POST['game'])     : '';
$userKey = isset($_POST['user_key']) ? trim((string) $_POST['user_key']) : '';
$serial  = isset($_POST['serial'])   ? trim((string) $_POST['serial'])   : '';

// ============================================================
// APP 1: PUBGM
// ============================================================
if ($game === 'PUBGM') {

    if ($userKey === '' || $serial === '') {
        respond(['status' => false, 'reason' => 'Missing user_key or serial'], 400);
    }

    $validKeys = ['demo123'];

    $configuredKeys = getenv('MUNDO_LICENSE_KEYS');
    if ($configuredKeys !== false && trim($configuredKeys) !== '') {
        $extraKeys = array_values(array_filter(array_map('trim', explode(',', $configuredKeys))));
        $validKeys = array_merge($validKeys, $extraKeys);
    }

    $keyIsValid = false;
    foreach ($validKeys as $validKey) {
        if (hash_equals($validKey, $userKey)) {
            $keyIsValid = true;
            break;
        }
    }

    if (!$keyIsValid) {
        respond(['status' => false, 'reason' => 'Invalid key'], 401);
    }

    $now = time();
    $expectedToken = md5('PUBGM-' . $userKey . '-' . $serial . '-MISHRAJI');

    respond([
        'status' => true,
        'reason' => 'OK',
        'data' => [
        'token' => $expectedToken,
        'rng' => $now,
        'EXP' => gmdate('Y-m-d H:i:s', $now + (365 * 86400)),
        'ESP' => 'ON',
        'Item' => 'ON',
        'AIM' => 'ON',
        'Memory' => 'ON',
        'Floating' => 'ON',
        'Setting' => 'ON',
        'SilentAim' => 'ON',
        'BulletTrack' => 'ON',
        'ALL_MENU_CODE' => ''
    ]
    ]);
}

// ============================================================
// APP 2: PUBG
// ============================================================
elseif ($game === 'PUBG') {

    if ($userKey === '' || $serial === '') {
        respond(['status' => false, 'reason' => 'Missing user_key or serial'], 400);
    }

    $AUTH_SECRET = 'Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E';
    $EXP_DAYS    = 30;
    $VALID_KEYS  = ['demo123'];

    $keyIsValid = false;
    foreach ($VALID_KEYS as $validKey) {
        if (hash_equals($validKey, $userKey)) {
            $keyIsValid = true;
            break;
        }
    }

    if (!$keyIsValid) {
        respond(['status' => false, 'reason' => 'Invalid key'], 401);
    }

    if (strlen($serial) < 10 || strlen($serial) > 100) {
        respond(['status' => false, 'reason' => 'Invalid serial'], 400);
    }

    $now   = time();
    $token = md5('PUBG-' . $userKey . '-' . $serial . '-' . $AUTH_SECRET);

    respond([
        'status' => true,
        'reason' => 'OK',
        'data' => [
            'token' => $token,
            'EXP'   => date('Y-m-d H:i:s', $now + ($EXP_DAYS * 86400)),
            'rng'   => $now
        ]
    ]);
}

// ============================================================
// Unknown game
// ============================================================
else {
    respond(['status' => false, 'reason' => 'Invalid game'], 400);
}
