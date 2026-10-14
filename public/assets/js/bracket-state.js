(function (root) {
  "use strict";

  function teamFor(definition, picks, slot) {
    const entry = definition[slot];

    if (!entry) {
      return null;
    }

    if (!entry.feeders.length) {
      return entry.team_id;
    }

    for (const feeder of entry.feeders) {
      if (picks[feeder]) {
        return picks[feeder];
      }
    }

    return null;
  }

  // State is a slot-to-winner map. The server definition owns all topology.
  function chooseWinner(definition, picks, slot) {
    const next = Object.assign({}, picks);
    const entry = definition[slot];
    const team = teamFor(definition, picks, slot);

    if (!entry || !team) {
      return next;
    }

    for (const selected of Object.keys(next)) {
      const other = definition[selected];

      if (
        !other ||
        (entry.region === other.region && entry.round === other.round && entry.game === other.game)
      ) {
        delete next[selected];
      }
    }

    next[slot] = team;

    // Iterate to a fixed point so invalid descendants disappear even if the
    // selected slots arrived in a different order (e.g. restored state).
    let changed;

    do {
      changed = false;

      for (const selected of Object.keys(next)) {
        if (teamFor(definition, next, selected) !== next[selected]) {
          delete next[selected];
          changed = true;
        }
      }
    } while (changed);

    return next;
  }

  const state = { teamFor: teamFor, chooseWinner: chooseWinner };

  if (typeof module !== "undefined" && module.exports) {
    module.exports = state;
  } else {
    root.BracketState = state;
  }
})(typeof globalThis !== "undefined" ? globalThis : this);
