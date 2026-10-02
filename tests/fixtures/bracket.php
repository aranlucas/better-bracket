<?php

declare(strict_types=1);

// Standalone, synthetic fixture for Node's browser contract tests. No database.
require __DIR__ . '/../../app/Services/BracketService.php';

use App\Services\BracketService;

$catalogue = [];
foreach (BracketService::REGIONS as $index => $region) {
    for ($seed = 1; $seed <= 16; $seed++) {
        $catalogue[] = ['id' => $index * 16 + $seed, 'region' => $region, 'seed' => $seed, 'team_name' => $region . ' ' . $seed];
    }
}
$definition = BracketService::definition($catalogue);
if (($argv[1] ?? '') === 'validate') {
    $picks = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
    echo json_encode(BracketService::validatePicks($picks, $catalogue), JSON_THROW_ON_ERROR);
    exit;
}
$teams = BracketService::teamsByRegion($catalogue);
$groups = [['id' => 1, 'name' => 'Synthetic test group']];
function view(string $name, array $data): string { return ''; }
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function base_url(string $path): string { return '/' . $path; }
function csrf_hash(): string { return 'synthetic-token'; }
function csrf_field(): string { return ''; }
require __DIR__ . '/../../app/Views/bracket/index.php';
