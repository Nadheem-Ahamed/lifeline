document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var navigation = document.querySelector('#primary-nav');

    if (toggle && navigation) {
        toggle.addEventListener('click', function () {
            var isOpen = navigation.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    }
});
