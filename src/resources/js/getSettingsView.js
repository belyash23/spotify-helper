export default function getView() {
    return new Promise((resolve, reject) => {
        // const Telegram = {
        //     WebApp: {
        //         "initData": "query_id=AAF-Mkk7AAAAAH4ySTtzxMGz&user=%7B%22id%22%3A994652798%2C%22first_name%22%3A%22%D0%90%D0%BD%D1%82%D0%BE%D0%BD%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22belyash42%22%2C%22language_code%22%3A%22ru%22%7D&auth_date=1652110770&hash=ab4cf801cb77a626785756a979b601bcc04d7a61edafa3fb0600fc296819bdd0",
        //         "initDataUnsafe": {
        //             "query_id": "AAF-Mkk7AAAAAH4ySTtzxMGz",
        //             "user": {
        //                 "id": 994652798,
        //                 "first_name": "Антон",
        //                 "last_name": "",
        //                 "username": "belyash42",
        //                 "language_code": "ru"
        //             },
        //             "auth_date": "1652110770",
        //             "hash": "ab4cf801cb77a626785756a979b601bcc04d7a61edafa3fb0600fc296819bdd0"
        //         },
        //         "version": "1.0",
        //         "colorScheme": "light",
        //         "themeParams": {
        //             "bg_color": "#ffffff",
        //             "button_color": "#40a7e3",
        //             "button_text_color": "#ffffff",
        //             "hint_color": "#999999",
        //             "link_color": "#168acd",
        //             "text_color": "#000000"
        //         },
        //         "isExpanded": true,
        //         "viewportHeight": 496,
        //         "viewportStableHeight": 496,
        //         "MainButton": {
        //             "text": "CONTINUE",
        //             "color": "#40a7e3",
        //             "textColor": "#ffffff",
        //             "isVisible": false,
        //             "isProgressVisible": false,
        //             "isActive": true
        //         }
        //     }
        // }
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
