import autocomplete from "./autocomplete";
import validateArtist from "./validateArtist";

export default function makeArtistNode() {
    const playlistArtist = document.createElement('div');
    playlistArtist.classList.add('playlist-artist');
    playlistArtist.innerHTML =
        `
            <input type="text" value="" class="playlist-artist__name">
            <button class="playlist-artist__remove" type="button">-</button>
        `;

    const playlistArtistInput = playlistArtist.querySelector('.playlist-artist__name');

    autocomplete(playlistArtistInput);
    playlistArtist.querySelector('button').addEventListener('click', function () {
        this.parentNode.remove();
    });
    playlistArtistInput.addEventListener('blur', validateArtist.bind(this, playlistArtistInput));

    return playlistArtist;
}
