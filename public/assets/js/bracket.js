(function () {
    'use strict';

    var form = document.getElementById('bracket-form');
    if (!form) return;

    var picks = {};
    var status = document.getElementById('bracket-status');
    var csrfToken = document.querySelector('meta[name="csrf-token"]');

    var definitionElement = document.getElementById('bracket-definition');
    if (!definitionElement) return;
    var definition = JSON.parse(definitionElement.textContent);
    var state = window.BracketState;
    var buttons = document.querySelectorAll('.team-choice, .champion-choice');
    var teams = {};
    var placeholders = {};

    buttons.forEach(function (button) {
        placeholders[button.dataset.slot] = button.querySelector('.team-name').textContent;
        if (button.dataset.teamId) {
            var seed = button.querySelector('.seed');
            teams[button.dataset.teamId] = {
                seed: seed ? seed.textContent : '',
                name: button.querySelector('.team-name').textContent
            };
        }
    });

    function render() {
        buttons.forEach(function (button) {
            var slot = button.dataset.slot;
            var teamId = state.teamFor(definition, picks, slot);
            var details = teams[teamId];
            button.dataset.teamId = details ? String(teamId) : '';
            button.disabled = !details;
            button.classList.toggle('is-selected', picks[slot] === teamId && !!details);
            var seed = button.querySelector('.seed');
            if (seed) seed.textContent = details ? details.seed : '';
            button.querySelector('.team-name').textContent = details ? details.name : placeholders[slot];
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!button.dataset.teamId) return;
            var slot = button.dataset.slot;
            picks = state.chooseWinner(definition, picks, slot);
            render();
            if (status) {
                status.textContent = slot === 'champion'
                    ? 'Champion selected. Save your picks when ready.'
                    : Object.keys(picks).length + ' selection' + (Object.keys(picks).length === 1 ? '' : 's') + ' ready to save.';
            }
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var group = document.getElementById('bracket-group');
        if (!group || !group.value) {
            if (status) status.textContent = 'Choose a group before saving your picks.';
            return;
        }

        var body = new URLSearchParams();
        body.set('group_id', group.value);
        body.set('picks', JSON.stringify(picks));
        body.set('csrf_token', form.dataset.csrfToken);
        if (status) status.textContent = 'Saving your picks…';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': form.dataset.csrfToken },
            body: body
        }).then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        }).then(function (result) {
            if (!result.ok || !result.data.ok) throw new Error(result.data.error || 'Picks could not be saved.');
            if (result.data.csrfHash && csrfToken) csrfToken.setAttribute('content', result.data.csrfHash);
            if (result.data.csrfHash) form.dataset.csrfToken = result.data.csrfHash;
            if (status) status.textContent = result.data.message;
        }).catch(function (error) {
            if (status) status.textContent = error.message;
        });
    });
}());
