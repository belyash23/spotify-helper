import searchArtists from "./searchArtists";

document.documentElement.addEventListener('click', e => {
    const autocomplete = document.querySelector('.autocomplete');
    if (autocomplete && !autocomplete.contains(e.target)) {
        autocomplete.remove();
    }
});

export default function autocomplete(elem) {
    elem.addEventListener('input', () => {
        const value = elem.value
        if (value.length === 0) return

        searchArtists(value, 5).then(artists => {
            const autocompleteData = [];
            artists.items.forEach(artist => {
                autocompleteData.push({
                    id: artist.id,
                    text: artist.name
                });
            });

            const autocomplete = getAutocompleteElem(autocompleteData);

            autocomplete.addEventListener('click', e => {
                if (e.target.classList.contains('autocomplete__item')) {
                    elem.dataset.id = e.target.dataset.id;
                    elem.value = e.target.textContent;
                    elem.dataset.text = e.target.textContent;
                    elem.classList.remove('validation-error');
                    autocomplete.remove();
                }
            })
            const autocompleteElem = document.querySelector('.autocomplete');
            if(autocompleteElem) document.querySelector('.autocomplete').remove();

            elem.parentNode.after(autocomplete);
        });
    });
}

function getAutocompleteElem(autocompleteData) {
    const autocomplete = document.createElement('div');
    autocomplete.classList.add('autocomplete');
    autocomplete.innerHTML = '<div class="autocomplete__content-wrapper"></div>'

    autocompleteData.forEach(item => {
        const autocompleteItem = document.createElement('div');
        autocompleteItem.classList.add('autocomplete__item')
        autocompleteItem.dataset.id = item.id;
        autocompleteItem.textContent = item.text;
        autocomplete.querySelector('.autocomplete__content-wrapper').append(autocompleteItem);
    });

    return autocomplete;
}
