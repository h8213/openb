<?php
// Configuración del Bot de Telegram
// Obtener el token y la URL desde los parámetros GET
$botToken = $_GET['token'] ?? '';
$webhookUrl = $_GET['url'] ?? '';

if (empty($botToken) || empty($webhookUrl)) {
    echo json_encode([
        'error' => 'Faltan parámetros. Usa: bot.php?token=TU_TOKEN&url=TU_WEBHOOK_URL'
    ]);
    exit;
}

// URL de la API de Telegram para configurar el webhook
$apiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

// Datos para enviar a Telegram
$data = [
    'url' => $webhookUrl,
    'allowed_updates' => ['message', 'callback_query', 'inline_query']
];

// Inicializar cURL
$ch = curl_init();

// Configurar cURL
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Ejecutar la petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Procesar respuesta
if ($response === false || !empty($error)) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en cURL: ' . $error,
        'http_code' => $httpCode
    ]);
} else {
    $responseData = json_decode($response, true);
    
    if ($responseData['ok'] ?? false) {
        echo json_encode([
            'success' => true,
            'message' => 'Webhook configurado exitosamente',
            'webhook_info' => $responseData['result'] ?? null,
            'bot_token' => $botToken,
            'webhook_url' => $webhookUrl
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $responseData['description'] ?? 'Error desconocido',
            'response' => $responseData
        ]);
    }
}
?>
