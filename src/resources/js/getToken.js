export default function getToken() {
    return new Promise((resolve, reject) => {
        const cacheData = JSON.parse(localStorage.getItem('token'));
        if(cacheData) {
            const tokenLifetime = 3500000;
            if (Date.now() - cacheData.date <= tokenLifetime) {
                resolve(cacheData.token);
                return;
            }
        }
        fetch('/api/getToken', {
            method: 'GET',
        }).then(response => {
            const status = response.status;
            switch (status) {
                case 200:
                    const token = response.text();



                    resolve(token);
                    break
                default:
                    resolve(false)
            }
        }).then(token => {
            const cacheData = {
                date: Date.now(),
                token: token
            }
            localStorage.setItem('token', JSON.stringify(cacheData));
            return token;
        });
    })
}
