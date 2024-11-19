document.addEventListener('DOMContentLoaded', function() {
    const eyes = document.querySelectorAll('.eye');
    eyes.forEach((eye) => {
        eye.addEventListener('click', function() {
            let state = 'open';
            if (eye.src.split('-')[1].split('.')[0] === "open") {
                state = 'closed'
            }
            eye.src = '/img/svg/eye-' + state + '.svg';

            if (state === 'open') {
                eye.previousElementSibling.type = 'text'
            } else {
                eye.previousElementSibling.type = 'password'
            }
        });
    });
});