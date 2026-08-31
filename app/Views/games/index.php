<?= view('layouts/header', ['title' => 'Past games']) ?>
<section class="container page-heading compact-heading"><div><div class="eyebrow">The record</div><h1>Past games</h1><p class="lede">A quick look back at the games on this board.</p></div></section>
<section class="container section-block">
    <div class="card table-card">
        <?php if ($games === []): ?><div class="empty-state"><span class="empty-icon">◷</span><h2>No games recorded yet</h2><p>Scores will appear here once the tournament data is loaded.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Date</th><th>Matchup</th><th class="score-column">Score</th></tr></thead><tbody><?php foreach ($games as $game): ?><tr><td><?= e(date('M j, Y', strtotime((string) $game['date_played']))) ?></td><td><strong><?= e($game['team_1']) ?></strong><span class="versus">vs</span><?= e($game['team_2']) ?></td><td class="score-column"><strong><?= (int) $game['score_1'] ?>–<?= (int) $game['score_2'] ?></strong></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </div>
</section>
<?= view('layouts/footer') ?>
