<?php

$cache = __DIR__ . '/../.phpunit.results.cache';
if (!file_exists($cache)) {
    $content = shell_exec('./vendor/bin/phpunit --coverage-text');
    file_put_contents($cache, $content);
} else {
    $content = file_get_contents($cache);
}

preg_match_all('|^\d+\) Hamlet\\\\Http\\\\Message\\\\([^\s]+)Test::|m', $content, $matches);
$stats = [];
foreach ($matches[1] as $name) {
    if (preg_match('/(.*?)(Request|Response|ServerRequest|Stream|UploadedFile|Uri)/', $name, $parts)) {
        $key = $parts[1];
        $stats[$key] = ($stats[$key] ?? 0) + 1;
    } else {
        die($name);
    }
}
asort($stats);

$header = "+-----------------+-------+\n";
echo $header;
foreach ($stats as $key => $count) {
    printf("| %-15s | %5d |\n", $key, $count);
}
echo $header;
