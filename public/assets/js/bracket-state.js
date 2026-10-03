(function (root) {
    'use strict';

    function teamFor(definition, picks, slot) {
        var entry = definition[slot];

        if (!entry) return null;

        if (!entry.feeders.length) return entry.team_id;

        for (var i = 0; i < entry.feeders.length; i++) {
            if (picks[entry.feeders[i]]) return picks[entry.feeders[i]];
        }

        return null;
    }

    // State is a slot-to-winner map. The server definition owns all topology.
    function chooseWinner(definition, picks, slot) {
        var next = Object.assign({}, picks);
        var entry = definition[slot];
        var team = teamFor(definition, picks, slot);

        if (!entry || !team) return next;

        Object.keys(next).forEach(function (selected) {
            var other = definition[selected];

            if (!other || (entry.region === other.region && entry.round === other.round && entry.game === other.game)) {
                delete next[selected];
            }
        });
        next[slot] = team;

        // Iterate to a fixed point so invalid descendants disappear even if the
        // selected slots arrived in a different order (e.g. restored state).
        var changed;

        do {
            changed = false;
            Object.keys(next).forEach(function (selected) {
                if (teamFor(definition, next, selected) !== next[selected]) {
                    delete next[selected];
                    changed = true;
                }
            });
        } while (changed);

        return next;
    }

    var state = { teamFor: teamFor, chooseWinner: chooseWinner };

    if (typeof module !== 'undefined' && module.exports) module.exports = state;
    else root.BracketState = state;
}(typeof globalThis !== 'undefined' ? globalThis : this));
