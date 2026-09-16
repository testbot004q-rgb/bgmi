<?php
// index.php - Authentication Server for Game.Loader
// Must match the logic in Login.java check() and ModsLoader.h getheaders()

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond([
        'status' => false,
        'reason' => 'POST request required'
    ], 405);
}

$game = isset($_POST['game']) ? trim((string) $_POST['game']) : '';
$userKey = isset($_POST['user_key']) ? trim((string) $_POST['user_key']) : '';
$serial = isset($_POST['serial']) ? trim((string) $_POST['serial']) : '';

if ($game !== 'PUBGM') {
    $validKeys = ['demo123'];
    if ($userKey === '' || $serial === '') {
        respond([
            'status' => false,
            'reason' => 'Missing user_key or serial'
        ], 400);
    }
    
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
        respond([
            'status' => false,
            'reason' => 'Invalid key'
        ]);
    }
    
    $now = time();
    $expectedToken = md5('PUBG-' . $userKey . '-' . $serial . '-MISHRAJI');
    
    respond([
        'status' => true,
        'data' => [
            'token' => $expectedToken,
            'rng' => $now,
            'EXP' => gmdate('Y-m-d', $now + (365 * 86400)),
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
}else{
    $GAME_NAME = 'PUBG';
    $AUTH_SECRET = 'Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E';
    
    // Valid user keys (add as many as needed)
    $VALID_KEYS = [
        'demo123',
        // add more keys here
    ];
    
    // Token expiry in days
    $EXP_DAYS = 30;
    
    // Get POST parameters

    
    // Validate game
    if ($game !== $GAME_NAME) {
        echo json_encode([
            'status' => false,
            'reason' => 'Invalid game'
        ]);
        exit;
    }
    
    // Validate user key
    if (!in_array($userKey, $VALID_KEYS, true)) {
        echo json_encode([
            'status' => false,
            'reason' => 'Invalid key'
        ]);
        exit;
    }
    
    // Validate serial (basic check - not empty and reasonable length)
    if (empty($serial) || strlen($serial) < 10 || strlen($serial) > 100) {
        echo json_encode([
            'status' => false,
            'reason' => 'Invalid serial'
        ]);
        exit;
    }
    
    // Generate token exactly as client expects:
    // md5(gameName + "-" + userKey + "-" + serial + "-" + authSecret)
    $token = md5($GAME_NAME . '-' . $userKey . '-' . $serial . '-' . $AUTH_SECRET);
    
    // Generate expiry date
    $exp_date = date('Y-m-d H:i:s', time() + ($EXP_DAYS * 86400));
    
    // Generate random rng (unix timestamp)
    $rng = time();
    
    // Return success response
    $response = [
        'status' => true,
        'reason' => 'OK',
        'data' => [
            'token' => $token,
            'EXP' => $exp_date,
            'rng' => $rng
        ]
    ];
    
    echo json_encode($response);
    ?>
}
?>
