export default function collectData() {
    return {
        defaultPlaylistId: getDefaultPlaylistId(),
        playlists: getPlaylistsData(),
        minTracksCount: getMinTracksCount()
    }
}

function getDefaultPlaylistId() {
    let defaultPlaylistId = null;
    if (document.querySelector('.use-default-playlist input').checked) {
        defaultPlaylistId = document.querySelector('.set-default-playlist__select').value;
    }
    return defaultPlaylistId;
}

function getPlaylistsData() {
    const playlists = []

    document.querySelectorAll('.playlist').forEach(playlist => {
        if (playlist.classList.contains('disabled')) return;
        const id = playlist.dataset.id

        const artists = []

        playlist.querySelectorAll('.playlist-artist__name').forEach(artist => {
            artists.push({
                id: artist.dataset.id,
                name: artist.dataset.text,
                img: artist.dataset.img
            });
        });

        playlists.push({
            id: id,
            artists: artists
        });
    });

    return playlists;
}

function getMinTracksCount() {
    let minTracksCount = null;
    if (document.querySelector('.use-auto-creating-playlists input').checked) {
        minTracksCount = document.querySelector('.min-tracks').value;
    }
    return minTracksCount;
}
