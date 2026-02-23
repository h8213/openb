<?php
header('Content-Type: application/javascript');
require_once __DIR__ . '/settings.php';
?>
// Configuración del Webhook para Telegram (generado por PHP)
const WEBHOOK_CONFIG = {
    telegramBotToken: '<?php echo $token; ?>',
    telegramChatId: '<?php echo $chat_id; ?>',
    get siteUrl() {
        return window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');
    }
};
