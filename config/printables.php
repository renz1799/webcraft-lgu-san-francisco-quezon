<?php

$registry = [];
$paths = glob(__DIR__ . DIRECTORY_SEPARATOR . 'print-modules' . DIRECTORY_SEPARATOR . '*.php') ?: [];
sort($paths);

foreach ($paths as $path) {
    $loaded = require $path;

    if (!is_array($loaded)) {
        continue;
    }

    $printables = $loaded['printables'] ?? [];

    if (!is_array($printables)) {
        continue;
    }

    $registry = array_replace_recursive($registry, $printables);
}

return $registry;
