export default function saveSettings(telegramId, settings) {
    fetch('/api/saveSettings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            telegramId: telegramId,
            settings: settings
        })
    });
}
