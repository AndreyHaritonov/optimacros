<?php

require __DIR__ . '/vendor/autoload.php';

if (count($argv) !== 3) {
    echo "Use: run.php <input.csv> <output.json>";
    exit(1);
}

$repo = new Andrey\Optimacros\Repo();

$repo->loadFromFile($argv[1]);
echo "Loaded rows: {$repo->getCount()}\n";

$res = $repo->saveToJson($argv[2]);
echo "Saved bytes: {$res}\n";

