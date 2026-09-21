#!/usr/bin/env php
<?php

$source = $argv[1] ?? null;

if (!$source) {
    fwrite(STDERR, "Usage: strip-eligibility-json.php <source-file>\n");
    exit(1);
}

$institutions = json_decode(file_get_contents($source), true);

foreach ($institutions as &$institution) {
    unset($institution['has-deal'], $institution['until']);
}

echo json_encode($institutions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
