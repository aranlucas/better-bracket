<?php declare(strict_types=1); ?>
<?= view('layouts/header', ['title' => 'Make your picks', 'description' => 'Choose a winner for every tournament matchup.']) ?>
<?php
$regions = ['south', 'west', 'east', 'midwest'];
$regionNumbers = array_flip($regions);
$roundLabels = [1 => 'Round of 64', 2 => 'Round of 32', 3 => 'Sweet 16', 4 => 'Elite 8'];
$gamesByRound = [1 => 8, 2 => 4, 3 => 2, 4 => 1];
?>
<section class="container page-heading compact-heading">
    <div><div class="eyebrow">Your tournament path</div><h1>Make your picks</h1><p class="lede">Tap a team to move it through the bracket. Your progress stays here until you save it.</p></div>
</section>
<section class="container bracket-controls card">
    <div class="field"><label for="bracket-group">Save picks to</label><select id="bracket-group" name="group_id" form="bracket-form" <?= $groups === [] ? 'disabled' : '' ?>><option value="">Choose a group</option><?php foreach ($groups as $group): ?><option value="<?= (int) $group['id'] ?>"><?= e($group['name']) ?></option><?php endforeach; ?></select></div>
    <?php if ($groups === []): ?><p class="control-note">Create a group before saving picks. <a class="text-link" href="<?= base_url('groups/new') ?>">Create one now →</a></p><?php else: ?><p id="bracket-status" class="control-note" role="status">No picks saved yet.</p><?php endif; ?>
    <form id="bracket-form" action="<?= base_url('bracket/picks') ?>" method="post" data-csrf-token="<?= e(csrf_hash()) ?>">
        <?= csrf_field() ?>
        <button class="button button-primary" type="submit" <?= $groups === [] ? 'disabled' : '' ?>>Save picks <span aria-hidden="true">→</span></button>
    </form>
</section>
<section class="bracket-board container" aria-label="Tournament bracket">
    <?php foreach ($regions as $regionIndex => $region): ?>
        <?php
        $regionNumber = $regionIndex + 1;
        $teamBySeed = [];
        foreach ($teams[$region] as $team) {
            $teamBySeed[(int) $team['seed']] = $team;
        }
        ?>
        <section class="bracket-region" aria-labelledby="region-<?= $regionNumber ?>">
            <div class="bracket-region-heading"><span class="region-number">0<?= $regionNumber ?></span><h2 id="region-<?= $regionNumber ?>"><?= e(ucfirst($region)) ?></h2></div>
            <div class="bracket-rounds">
                <?php foreach ($gamesByRound as $round => $gameCount): ?>
                    <div class="bracket-round">
                        <h3><?= e($roundLabels[$round]) ?></h3>
                        <div class="bracket-games round-<?= $round ?>">
                            <?php for ($game = 1; $game <= $gameCount; $game++): ?>
                                <div class="bracket-game" data-region="<?= $regionNumber ?>" data-round="<?= $round ?>" data-game="<?= $game ?>">
                                    <?php for ($slot = 1; $slot <= 2; $slot++): ?>
                                        <?php
                                        $team = null;
                                        if ($round === 1) {
                                            $seed = (int) \App\Services\BracketService::OPENING_MATCHUPS[$game - 1][$slot];
                                            $team = $teamBySeed[$seed] ?? null;
                                        }
                                        $slotId = $regionNumber . '-' . $round . '-' . $game . '-' . $slot;
                                        ?>
                                        <button type="button" class="team-choice" data-slot="<?= e($slotId) ?>" data-team-id="<?= $team ? (int) $team['id'] : '' ?>" <?= $team ? '' : 'disabled' ?>>
                                            <span class="seed"><?= $team ? (int) $team['seed'] : '' ?></span><span class="team-name"><?= $team ? e($team['team_name']) : 'Winner advances here' ?></span>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="finals-panel" aria-labelledby="finals-heading">
        <div class="bracket-region-heading"><span class="region-number accent-number">★</span><h2 id="finals-heading">Finals</h2></div>
        <div class="finals-grid">
            <div class="bracket-round"><h3>Final Four</h3><div class="bracket-game"><button type="button" class="team-choice" data-slot="1-5-1-1" data-team-id="" disabled><span class="seed"></span><span class="team-name">South winner</span></button><button type="button" class="team-choice" data-slot="1-5-1-2" data-team-id="" disabled><span class="seed"></span><span class="team-name">West winner</span></button></div><div class="bracket-game"><button type="button" class="team-choice" data-slot="2-5-1-1" data-team-id="" disabled><span class="seed"></span><span class="team-name">East winner</span></button><button type="button" class="team-choice" data-slot="2-5-1-2" data-team-id="" disabled><span class="seed"></span><span class="team-name">Midwest winner</span></button></div></div>
            <div class="bracket-round"><h3>Championship</h3><div class="bracket-game championship-game"><button type="button" class="team-choice" data-slot="3-5-1-1" data-team-id="" disabled><span class="seed"></span><span class="team-name">Finalist one</span></button><button type="button" class="team-choice" data-slot="3-5-1-2" data-team-id="" disabled><span class="seed"></span><span class="team-name">Finalist two</span></button></div><button type="button" class="champion-choice" id="champion" data-slot="champion" data-team-id="" disabled><span class="champion-label">Champion</span><span class="team-name">Make it all the way</span></button></div>
        </div>
    </section>
</section>
<?= view('layouts/footer', ['scripts' => ['assets/js/bracket.js']]) ?>
