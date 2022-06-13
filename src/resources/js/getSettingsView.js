export default function getView() {
    return new Promise((resolve, reject) => {
        fetch('/api/getSettingsView', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Telegram.WebApp)
        }).then(response => {
            const status = response.status;
            switch (status) {
                case 200:
                    resolve(response.text());
                    break
                case 422:
                    resolve(false)
            }

        });
    })
}
