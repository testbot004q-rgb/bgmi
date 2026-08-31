<?php
// index.php - Authentication Server for Game.Loader
// Must match the logic in Login.java check() and ModsLoader.h getheaders()

header('Content-Type: application/json; charset=utf-8');

// Configuration (must match ModsLoader.h values)
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
$game = $_POST['game'] ?? '';
$user_key = $_POST['user_key'] ?? '';
$serial = $_POST['serial'] ?? '';

// Validate game
if ($game !== $GAME_NAME) {
    echo json_encode([
        'status' => false,
        'reason' => 'Invalid game'
    ]);
    exit;
}

// Validate user key
if (!in_array($user_key, $VALID_KEYS, true)) {
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
$token = md5($GAME_NAME . '-' . $user_key . '-' . $serial . '-' . $AUTH_SECRET);

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
