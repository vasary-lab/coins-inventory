#!/usr/bin/env php
<?php

declare(strict_types=1);

const CHANGELOG = __DIR__ . '/../docs/CHANGELOG.md';

$dryRun = \in_array('--dry-run', $argv, true);
$noVerify = \in_array('--no-verify', $argv, true);

function run(string $command, bool $allowFailure = false): string
{
    $output = [];
    $code = 0;

    exec($command . ' 2>&1', $output, $code);

    if (0 !== $code && !$allowFailure) {
        fwrite(STDERR, implode("\n", $output) . "\n");
        exit($code);
    }

    return trim(implode("\n", $output));
}

function git(string $args, bool $allowFailure = false): string
{
    return run('git ' . $args, $allowFailure);
}

function title(string $value): string
{
    $value = str_replace(['-', '_'], ' ', $value);

    return ucfirst($value);
}

$status = git('status --porcelain');
if (!$dryRun && '' !== $status) {
    fwrite(STDERR, "Working tree must be clean before release.\n");
    exit(1);
}

$latestTag = git("tag --sort=-v:refname --list '[0-9]*' | head -n 1");
if ('' === $latestTag) {
    fwrite(STDERR, "No semver release tag found.\n");
    exit(1);
}

$commitsRaw = git(sprintf('log --no-merges --format=%%H%%x00%%s%%x00%%b%%x1e %s..HEAD', escapeshellarg($latestTag)));
if ('' === $commitsRaw) {
    fwrite(STDERR, "No commits found since {$latestTag}.\n");
    exit(1);
}

$commits = [];
$bump = 'patch';

foreach (array_filter(explode("\x1e", $commitsRaw)) as $entry) {
    [$hash, $subject, $body] = array_pad(explode("\x00", trim($entry), 3), 3, '');

    if (!preg_match('/^(?<type>[a-z]+)(?:\((?<scope>[^)]+)\))?(?<breaking>!)?: (?<message>.+)$/', $subject, $matches)) {
        continue;
    }

    if ('chore' === $matches['type'] && str_starts_with($matches['message'], 'release:')) {
        continue;
    }

    $isBreaking = '!' === ($matches['breaking'] ?? '') || str_contains($body, 'BREAKING CHANGE');
    if ($isBreaking) {
        $bump = 'major';
    } elseif ('major' !== $bump && 'feat' === $matches['type']) {
        $bump = 'minor';
    }

    $commits[] = [
        'hash' => substr($hash, 0, 7),
        'type' => $matches['type'],
        'scope' => $matches['scope'] ?? '',
        'message' => ucfirst($matches['message']),
    ];
}

if ([] === $commits) {
    fwrite(STDERR, "No conventional commits found since {$latestTag}.\n");
    exit(1);
}

[$major, $minor, $patch] = array_map('intval', explode('.', $latestTag));
if ('major' === $bump) {
    ++$major;
    $minor = 0;
    $patch = 0;
} elseif ('minor' === $bump) {
    ++$minor;
    $patch = 0;
} else {
    ++$patch;
}

$nextTag = "{$major}.{$minor}.{$patch}";
$sections = [
    'feat' => 'Features',
    'fix' => 'Bug Fixes',
    'perf' => 'Performance',
    'refactor' => 'Refactoring',
    'docs' => 'Documentation',
    'test' => 'Tests',
    'style' => 'Styles',
    'build' => 'Builds',
    'ci' => 'Continuous Integration',
    'chore' => 'Chores',
];

$grouped = [];
foreach ($commits as $commit) {
    $grouped[$commit['type']][] = $commit;
}

$release = sprintf("## %s (%s)\n\n", $nextTag, date('Y-m-d'));
foreach ($sections as $type => $heading) {
    if (!isset($grouped[$type])) {
        continue;
    }

    $release .= "### {$heading}\n\n";
    foreach ($grouped[$type] as $commit) {
        $scope = '' !== $commit['scope'] ? "**{$commit['scope']}:** " : '';
        $release .= sprintf("* %s%s (%s)\n", $scope, $commit['message'], $commit['hash']);
    }
    $release .= "\n";
}
$release .= "\n---\n\n";

$changelog = file_get_contents(CHANGELOG);
if (false === $changelog) {
    fwrite(STDERR, "Unable to read docs/CHANGELOG.md.\n");
    exit(1);
}

$marker = "<!--- END HEADER -->\n\n";
if (!str_contains($changelog, $marker)) {
    fwrite(STDERR, "Unable to find changelog header marker.\n");
    exit(1);
}

$updatedChangelog = str_replace($marker, $marker . $release, $changelog);

if ($dryRun) {
    echo "Next release: {$nextTag}\n\n";
    echo $release;
    exit(0);
}

file_put_contents(CHANGELOG, $updatedChangelog);

git('add docs/CHANGELOG.md');
$verifyFlag = $noVerify ? ' --no-verify' : '';
git(sprintf('commit%s -m %s', $verifyFlag, escapeshellarg("chore(release): {$nextTag}")));
git(sprintf('tag %s', escapeshellarg($nextTag)));

echo "Created release {$nextTag}\n";
