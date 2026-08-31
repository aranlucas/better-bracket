<?php
$title = $title ?? 'Better Bracket';
$description = $description ?? 'Build, share, and follow tournament brackets with your groups.';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(csrf_hash()) ?>">
    <title><?= e($title) ?> · Better Bracket</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<?= view('layouts/navbar') ?>
<main class="page-shell">
    <?= view('layouts/flash') ?>
