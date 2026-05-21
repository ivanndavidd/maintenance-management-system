<?php
$vendorPath = __DIR__ . '/../vendor';
$manifests = [];
$dirs = glob($vendorPath . '/*/*/composer.json');
$dontDiscover = ['laravel/pail', 'laravel/sail', 'nunomaduro/collision'];
foreach ($dirs as $path) {
    $manifest = json_decode(file_get_contents($path), true);
    if (!isset($manifest['extra']['laravel'])) continue;
    $pkg = $manifest['name'];
    if (in_array($pkg, $dontDiscover)) continue;
    $manifests[$pkg] = $manifest['extra']['laravel'];
}
$content = '<?php return ' . var_export($manifests, true) . ';' . PHP_EOL;
@mkdir(__DIR__ . '/../bootstrap/cache', 0755, true);
file_put_contents(__DIR__ . '/../bootstrap/cache/packages.php', $content);
echo 'packages.php generated with ' . count($manifests) . ' packages' . PHP_EOL;
