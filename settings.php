<?php
// Configuración global del bot de Telegram
$token = '8587374664:AAHqJtjHF_kvUjPKFX_oafSU3RbWwkmKI_Y';
$chat_id = '7758189913';

// URL base del sitio (auto-detectada)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$SITE_URL = $protocol . '://' . $host;

// Directorio de sesiones para polling
$SESSIONS_DIR = __DIR__ . '/sessions/';
if (!is_dir($SESSIONS_DIR)) {
    mkdir($SESSIONS_DIR, 0777, true);
}

function writeSession($sessionId, $action) {
    global $SESSIONS_DIR;
    $file = $SESSIONS_DIR . preg_replace('/[^a-zA-Z0-9_\-@.]/', '_', $sessionId) . '.json';
    file_put_contents($file, json_encode([
        'action' => $action,
        'time' => time()
    ]));
}
?>
