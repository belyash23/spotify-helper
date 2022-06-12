import getToken from "./getToken";

export default function searchArtists(query, limit) {
    return new Promise(resolve => {
        getToken().then(token => {
            const params = new URLSearchParams({
                q: query,
                type: "artist",
                limit: limit,
            });
            fetch(`https://api.spotify.com/v1/search?${params}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
                .then(response => response.json())
                .then(data => resolve(data.artists));
        });
    });
}
