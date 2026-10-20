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
        'ALL_MENU_CODE' => 'ON'
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
// APP 3: VIP
// ============================================================
elseif ($game === 'VIP') {
    if ($userKey === '' || $serial === '' || $game === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing user_key, serial, or game.'
    ]);
    exit;
}

$validKeys = ['admin', 'demo123', '123456'];
$valid = in_array($userKey, $validKeys, true);

if (!$valid) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid key.'
    ]);
    exit;
}

$token = hash('sha256', $key . ':' . $uuid . ':' . time());

echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'data' => [
        'token' => $token,
        'rng' => time() + 3600,
        'EXP' => '9999'
    ]
]);
}

    
// ============================================================
// APP 3: YADAV
// ============================================================

elseif ($game === 'YADAV') {
    if ($userKey === '' || $serial === '' || strlen($userKey) > 256 || strlen($serial) > 128) {
        respond(['status' => false, 'reason' => 'Invalid request'], 400);
    }

    $sharedSecret = getenv('LOGIN_SHARED_SECRET');
    if ($sharedSecret === false || $sharedSecret === '') {
        respond(['status' => false, 'reason' => 'Authentication service unavailable'], 500);
    }

    $predefinedKeys = [
        'demo123' => '2099-12-31 23:59:59',
    ];

    if (!array_key_exists($userKey, $predefinedKeys)) {
        respond(['status' => false, 'reason' => 'Invalid license key'], 401);
    }

    $expiresAt = strtotime($predefinedKeys[$userKey]);
    if ($expiresAt === false || $expiresAt <= time()) {
        respond(['status' => false, 'reason' => 'License expired'], 401);
    }

    $token = md5('YADAV-' . $userKey . '-' . $serial . '-' . $sharedSecret);

    respond([
        'status' => true,
        'reason' => 'OK',
        'data' => [
            'token' => $token,
            'rng' => time(),
            'EXP' => date('Y-m-d H:i:s', $expiresAt),
        ],
    ]);
}




// ============================================================
// Unknown game
// ============================================================
else {
    respond(['status' => false, 'reason' => 'Invalid game'], 400);
}
