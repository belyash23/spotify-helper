import getView from './getSettingsView';
import makeArtistNode from "./makeArtistNode";
import autocomplete from "./autocomplete";
import collectData from './collectData';
import validateArtist from "./validateArtist";
import saveSettings from "./saveSettings";

getView().then(html => {
    if (html) {
        document.body.innerHTML = html;

        const form = document.querySelector('.settings-form');
        form.addEventListener('submit', e => e.preventDefault());

        const telegramId = form.dataset.telegramId;

        initDefaultPlaylist();
        autoCreatingPlaylists();

        document.querySelectorAll('.add-artist').forEach(item => {
            item.addEventListener('click', function () {
                const playlistArtist = makeArtistNode();
                this.before(playlistArtist);
            });
        });

        document.querySelectorAll('.playlist-artist__remove').forEach(item => {
            item.addEventListener('click', function () {
                this.parentNode.remove();

            });
        });

        document.querySelectorAll('.playlist-artist__name').forEach(item => autocomplete(item));
        document.querySelectorAll('.playlist-artist__name').forEach(item => {
            item.addEventListener('blur', validateArtist.bind(this, item));
        });

        document.querySelector('.send').addEventListener('click', () => {
            if (document.querySelector('.validation-error')) {
                document.querySelector('.error').hidden = false;
            } else {
                saveSettings(telegramId, collectData());
                Telegram.WebApp.close();
            }
        });

    } else {
        document.querySelector('.preloader').hidden = true;
        document.querySelector('.validating-error').hidden = false;
    }
});

function disablePlaylist(playlistId) {
    document.querySelector(`[data-id = "${playlistId}"]`).classList.add('disabled');
}

function enablePlaylist(playlistId) {
    document.querySelector(`[data-id = "${playlistId}"]`).classList.remove('disabled');
}

function initDefaultPlaylist() {
    const setDefaultPlaylist = document.querySelector('.set-default-playlist__select');
    let defaultPlaylistId = setDefaultPlaylist.value;

    disablePlaylist(defaultPlaylistId);

    setDefaultPlaylist.addEventListener('change', function () {
        enablePlaylist(defaultPlaylistId);
        defaultPlaylistId = this.value;
        disablePlaylist(defaultPlaylistId);
    });

    const useDefaultPlaylistInput = document.querySelector('.use-default-playlist input');
    if (!useDefaultPlaylistInput.checked) {
        setDefaultPlaylist.classList.add('disabled');
        enablePlaylist(defaultPlaylistId);
    }

    useDefaultPlaylistInput.addEventListener('change', function () {
        if (this.checked) {
            setDefaultPlaylist.classList.remove('disabled');
            disablePlaylist(defaultPlaylistId);
        } else {
            setDefaultPlaylist.classList.add('disabled');
            enablePlaylist(defaultPlaylistId);
        }
    });


}

function autoCreatingPlaylists() {
    const useAutoCreatingPlaylists = document.querySelector('.use-auto-creating-playlists input');
    const autoCreatingPlaylists = document.querySelector('.auto-creating-playlists input');

    if (!useAutoCreatingPlaylists.checked) {
        autoCreatingPlaylists.classList.add('disabled');
    }

    useAutoCreatingPlaylists.addEventListener('change', function () {
        if (this.checked) {
            autoCreatingPlaylists.classList.remove('disabled');
        } else {
            autoCreatingPlaylists.classList.add('disabled');
        }
    });
}
