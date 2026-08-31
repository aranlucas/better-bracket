(function () {
    'use strict';

    var form = document.getElementById('bracket-form');
    if (!form) return;

    var picks = {};
    var status = document.getElementById('bracket-status');
    var csrfToken = document.querySelector('meta[name="csrf-token"]');

    function destinationFor(region, round, game) {
        if (round < 4) {
            return region + '-' + (round + 1) + '-' + Math.ceil(game / 2) + '-' + (game % 2 === 1 ? 1 : 2);
        }
        if (round === 4) {
            var regionalFinals = {
                1: '1-5-1-1',
                2: '1-5-1-2',
                3: '2-5-1-1',
                4: '2-5-1-2'
            };
            return regionalFinals[region];
        }
        if (round === 5 && region < 3) {
            return region === 1 ? '3-5-1-1' : '3-5-1-2';
        }
        return 'champion';
    }

    function setTeam(slot, teamId, seed, name) {
        var target = document.querySelector('[data-slot="' + slot + '"]');
        if (!target) return;
        target.dataset.teamId = teamId;
        target.disabled = false;
        target.querySelector('.seed').textContent = seed || '';
        target.querySelector('.team-name').textContent = name;
    }

    function teamDetails(button) {
        return {
            id: button.dataset.teamId,
            seed: button.querySelector('.seed').textContent,
            name: button.querySelector('.team-name').textContent
        };
    }

    document.querySelectorAll('.team-choice, .champion-choice').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!button.dataset.teamId) return;

            var slot = button.dataset.slot;
            var details = teamDetails(button);
            picks[slot] = Number(details.id);
            document.querySelectorAll('[data-slot="' + slot + '"]').forEach(function (item) { item.classList.remove('is-selected'); });
            button.classList.add('is-selected');

            if (slot === 'champion') {
                if (status) status.textContent = 'Champion selected. Save your picks when ready.';
                return;
            }

            var parts = slot.split('-').map(Number);
            var destination = destinationFor(parts[0], parts[1], parts[2]);
            if (destination) setTeam(destination, details.id, details.seed, details.name);
            if (status) status.textContent = Object.keys(picks).length + ' pick' + (Object.keys(picks).length === 1 ? '' : 's') + ' ready to save.';
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
