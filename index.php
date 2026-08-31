<?php
/**
 * ============================================================
 *  FLAKER-JAVA Login API
 *  Fully working backend for the Android app (com.pubgm)
 * ============================================================
 *
 *  Request (form-encoded or JSON):
 *      game=PUBG&user_key=YOUR_KEY&serial=DEVICE_UUID
 *
 *  Response (success):
 *      { "status": true, "reason": "OK",
 *        "data": { "token": "<MD5>", "EXP": "...", "rng": ... } }
 *
 *  IMPORTANT:
 *  The Android app (Login.java check()) validates the token
 *  against the MD5 of:
 *      game + "-" + userKey + "-" + uuid + "-" + authSecret
 *  where authSecret is hardcoded in the native library.
 *  The PHP server MUST generate the SAME MD5 token.
 * ============================================================
 */

// ------------------------------------------------------------------
// Configuration (EDIT THESE VALUES)
// ------------------------------------------------------------------

// This MUST match the native OBFUSCATE value in ModsLoader.h
//   OBFUSCATE("Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E")
$AUTH_SECRET = 'Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E';

// Valid license keys (add your real keys here)
$ALLOWED_KEYS = [
    'demo123',
    'ABC123',
    'YOUR_LICENSE_KEY_HERE'
];

// License validity in days
$LICENSE_DAYS = 30;

// ------------------------------------------------------------------
// Helpers
// ------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function apiError($message, $httpCode = 400) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => false,
        'reason' => $message,
        'data'  => null
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function apiSuccess($data) {
    http_response_code(200);
    echo json_encode([
        'status' => true,
        'reason' => 'OK',
        'data'  => $data
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------------
// Request parsing (accepts form-encoded AND JSON)
// ------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Method not allowed', 405);
}

$raw = file_get_contents('php://input');
$form = [];

// Form-encoded: game=PUBG&user_key=KEY&serial=UUID
parse_str($raw, $form);

// JSON body: { "game": "PUBG", "user_key": "KEY", "serial": "UUID" }
$json = json_decode($raw, true);
if (is_array($json)) {
    $form = array_merge($form, $json);
}

// Also accept parameters from query string (?game=...&user_key=...&serial=...)
$form = array_merge($form, $_GET);

// ------------------------------------------------------------------
// Extract parameters (multiple naming conventions supported)
// ------------------------------------------------------------------

$game = trim((string)($form['game'] ?? 'PUBG'));
$key  = trim((string)(
    $form['user_key'] ??
    $form['key'] ??
    $form['license_key'] ??
    $form['game_key'] ??
    $form['license'] ??
    ''
));
$serial = trim((string)(
    $form['serial'] ??
    $form['device_id'] ??
    $form['device'] ??
    $form['uuid'] ??
    ''
));

if ($key === '') {
    apiError('Missing user_key');
}

if ($serial === '') {
    apiError('Missing serial');
}

// ------------------------------------------------------------------
// Validate license key
// ------------------------------------------------------------------

if (!in_array($key, $ALLOWED_KEYS, true)) {
    apiError('Invalid license key');
}

// ------------------------------------------------------------------
// Generate token (MUST match app's MD5 logic)
// ------------------------------------------------------------------
// App side:
//   String auth = game + "-" + userKey + "-" + uuid + "-" + authSecret;
//   g_Auth = getMD5(auth);
//   bValid = g_Token.equals(g_Auth);
// ------------------------------------------------------------------

$authString = $game . '-' . $key . '-' . $serial . '-' . $AUTH_SECRET;
$token = md5($authString);

// ------------------------------------------------------------------
// Generate expiry (the app parses many formats, see Login.java)
// ------------------------------------------------------------------

$expiry = date('Y-m-d H:i:s', time() + ($LICENSE_DAYS * 86400));

// ------------------------------------------------------------------
// Success response
// ------------------------------------------------------------------

apiSuccess([
    'token' => $token,
    'EXP'   => $expiry,
    'rng'   => time()
]);

