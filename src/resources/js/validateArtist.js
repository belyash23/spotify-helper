export default function validateArtist(input) {
    if (input.dataset.text && input.dataset.text === input.value) {
        input.classList.remove('validation-error');

    } else {
        input.classList.add('validation-error');
    }
}
