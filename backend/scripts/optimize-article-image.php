<?php
require __DIR__.'/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$src = $argv[1] ?? '';
$dst = $argv[2] ?? '';
if ($src === '' || $dst === '' || ! is_file($src)) {
    fwrite(STDERR, "Uso: php optimize-article-image.php <src> <dst.jpg>\n");
    exit(1);
}

$manager = new ImageManager(new Driver());
$manager->read($src)->scaleDown(1200, 1200)->toJpeg(85)->save($dst);
echo "OK: {$dst} (" . filesize($dst) . " bytes)\n";
