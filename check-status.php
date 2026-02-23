<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$SESSIONS_DIR = __DIR__ . '/sessions/';

$sessionId = $_GET['session'] ?? '';

if (empty($sessionId)) {
    echo json_encode(['action' => 'wait']);
    exit;
}

$file = $SESSIONS_DIR . preg_replace('/[^a-zA-Z0-9_\-@.]/', '_', $sessionId) . '.json';

if (!file_exists($file)) {
    echo json_encode(['action' => 'wait']);
    exit;
}

$data = json_decode(file_get_contents($file), true);

// Si el archivo tiene más de 5 minutos, ignorarlo
if (time() - ($data['time'] ?? 0) > 300) {
    unlink($file);
    echo json_encode(['action' => 'wait']);
    exit;
}

// Eliminar el archivo después de leerlo (one-time use)
unlink($file);

echo json_encode(['action' => $data['action'] ?? 'wait']);
?>
