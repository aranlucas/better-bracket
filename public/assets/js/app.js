(function () {
    'use strict';

    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    document.querySelectorAll('.flash').forEach(function (flash) {
        window.setTimeout(function () {
            flash.style.opacity = '0';
            window.setTimeout(function () { flash.remove(); }, 220);
        }, 6500);
    });
}());
