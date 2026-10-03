'use strict';

const assert = require('node:assert/strict');

const { execFileSync } = require('node:child_process');

const { readFileSync } = require('node:fs');

const { resolve } = require('node:path');

const { test } = require('node:test');

const vm = require('node:vm');

const state = require('../../public/assets/js/bracket-state.js');

const root = resolve(__dirname, '../..');

const fixture = resolve(root, 'tests/fixtures/bracket.php');

const html = execFileSync(process.env.PHP_BINARY || 'php', [fixture], { encoding: 'utf8' });

const definitionJSON = html.match(/id="bracket-definition">(.*?)<\/script>/s)[1];

const definition = JSON.parse(definitionJSON);

const path = ['1-1-1-1', '1-2-1-1', '1-3-1-1', '1-4-1-1', '1-5-1-1', '3-5-1-1', 'champion'];

function choose(slots, picks = {}) {
    return slots.reduce((result, slot) => state.chooseWinner(definition, result, slot), picks);
}

function validate(picks) {
    return JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [fixture, 'validate'], {
        input: JSON.stringify(picks), encoding: 'utf8'
    }));
}

test('rendered slots exactly match the server tournament definition', () => {
    const slots = [...html.matchAll(/data-slot="([^"]+)"/g)].map(match => match[1]);
    assert.equal(slots.length, 127);
    assert.deepEqual(slots.sort(), Object.keys(definition).sort());
});

test('replacing an opening winner clears selected descendants through champion', () => {
    const previous = choose([...path, '3-1-1-1']);
    const next = choose(['1-1-1-2'], previous);
    assert.deepEqual(next, { '3-1-1-1': 33, '1-1-1-2': 16 });
    assert.equal(previous.champion, 1, 'the prior state is not mutated');
    assert.equal(state.teamFor(definition, next, '1-2-1-1'), 16);
    assert.equal(state.teamFor(definition, next, 'champion'), null);
    assert.equal(validate(next).length, 2);
});

test('reselecting a winner preserves its descendants', () => {
    const previous = choose(path);
    assert.deepEqual(choose(['1-1-1-1'], previous), previous);
});

test('changing a losing branch preserves the winning branch and champion', () => {
    const previous = choose([...path, '1-1-2-1']);
    const next = choose(['1-1-2-2'], previous);
    assert.equal(next.champion, 1);
    assert.equal(next['1-1-2-2'], 9);
    assert.equal(validate(next).length, 8);
});

test('invalid and unfilled slots cannot produce a winner', () => {
    assert.deepEqual(choose(['1-4-8-1', '4-5-1-1', 'champion', '1-2-1-1']), {});
});

test('reversed selection order still clears all invalid descendants', () => {
    const previous = Object.fromEntries(Object.entries(choose(path)).reverse());
    assert.deepEqual(choose(['1-1-1-2'], previous), { '1-1-1-2': 16 });
});

// A small DOM adapter built from the actual PHP-rendered buttons. It intentionally
// has no seed element on champion, matching the production view contract.
function browser() {
    const buttons = [...html.matchAll(/<button[^>]*data-slot="([^"]+)"[^>]*data-team-id="([^"]*)"[^>]*>(.*?)<\/button>/gs)]
        .map(([, slot, teamId, content]) => {
            const seedMatch = content.match(/class="seed">(.*?)<\/span>/s);
            const name = { textContent: content.match(/class="team-name">(.*?)<\/span>/s)[1] };
            const seed = seedMatch ? { textContent: seedMatch[1] } : null;
            const classes = new Set();

            return {
                dataset: { slot, teamId }, disabled: !teamId, classes,
                querySelector: selector => selector === '.seed' ? seed : name,
                addEventListener(event, handler) { this[event] = handler; },
                classList: { add: value => classes.add(value), remove: value => classes.delete(value),
                    toggle(value, active) { if (active) classes.add(value); else classes.delete(value); } }
            };
        });

    const form = { action: '/bracket/picks', dataset: { csrfToken: 'synthetic-token' },
        addEventListener(event, handler) { this[event] = handler; } };

    const status = { textContent: '' };

    const ids = { 'bracket-form': form, 'bracket-status': status,
        'bracket-definition': { textContent: definitionJSON }, 'bracket-group': { value: '1' } };

    let submitted;

    function select(selector) {
        const exact = selector.match(/^\[data-slot="([^"]+)"\]$/);
        const prefix = selector.match(/^\[data-slot\^="([^"]+)"\]$/);

        if (exact) return buttons.filter(button => button.dataset.slot === exact[1]);

        if (prefix) return buttons.filter(button => button.dataset.slot.startsWith(prefix[1]));

        return selector === '.team-choice, .champion-choice' ? buttons : [];
    }

    const context = { window: { BracketState: state }, URLSearchParams,
        document: { getElementById: id => ids[id], querySelector: selector => select(selector)[0] || null, querySelectorAll: select },
        fetch(url, options) {
            submitted = JSON.parse(options.body.get('picks'));

            return Promise.resolve({ ok: true, json: () => Promise.resolve({ ok: true, message: 'Picks saved.' }) });
        }
    };

    vm.runInNewContext(readFileSync(resolve(root, 'public/assets/js/bracket.js'), 'utf8'), context);

    return {
        buttons, status, button: slot => buttons.find(button => button.dataset.slot === slot),
        click(slot) { const button = this.button(slot);

 if (!button.disabled) button.click(); },
        submit() { form.submit({ preventDefault() {} });

 return submitted; }
    };
}

test('full championship selection, repeated clicks and save work with a seedless champion', () => {
    const page = browser();

    for (const [slot, entry] of Object.entries(definition)) {
        if (entry.team === 1) page.click(slot);
    }

    page.click('champion');
    const champion = page.button('champion');
    assert.equal(champion.querySelector('.seed'), null);
    assert.equal(champion.querySelector('.team-name').textContent, 'south 1');
    assert.equal(champion.disabled, false);
    assert.equal(champion.classes.has('is-selected'), true);
    assert.match(page.status.textContent, /Champion selected/);
    assert.equal(validate(page.submit()).length, 64);
});

test('changing an early winner clears intermediate rounds before any champion is chosen', () => {
    const page = browser();
    path.slice(0, 4).forEach(slot => page.click(slot));
    page.click('1-1-1-2');
    assert.equal(page.button('1-3-1-1').disabled, true);
    assert.equal(page.button('1-4-1-1').disabled, true);
    assert.equal(page.button('1-4-1-1').classes.has('is-selected'), false);
    assert.deepEqual(page.submit(), { '1-1-1-2': 16 });
});

test('browser clears stale selections, names, enabled slots and saved picks after an upset', () => {
    const page = browser();
    path.forEach(slot => page.click(slot));
    page.click('1-1-1-2');
    assert.equal(page.button('1-2-1-1').dataset.teamId, '16');

    for (const slot of path.slice(2)) {
        assert.equal(page.button(slot).disabled, true, slot);
        assert.equal(page.button(slot).classes.has('is-selected'), false, slot);
        assert.equal(page.button(slot).dataset.teamId, '', slot);
    }

    assert.equal(page.button('champion').querySelector('.team-name').textContent, 'Make it all the way');
    assert.deepEqual(page.submit(), { '1-1-1-2': 16 });
    path.slice(1).forEach(slot => page.click(slot));
    assert.equal(page.button('champion').querySelector('.team-name').textContent, 'south 16');
    assert.equal(validate(page.submit()).length, 7);
});
