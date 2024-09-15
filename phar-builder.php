<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Finder\Finder;

$pharFile = "powerdi.phar";

if (file_exists($pharFile)) {
    unlink($pharFile);
}

$phar = new Phar($pharFile);
$phar->compressFiles(Phar::GZ);
$phar->setSignatureAlgorithm(Phar::SHA1);

$phar->startBuffering();

$finder = new Finder();
$finder->files()->ignoreVCS(true)->name('/.*\.(php|bash|fish|zsh)/')->in(__DIR__ . '/src')->in(__DIR__ . '/vendor');

foreach ($finder as $file) {
    $path = $file->getRealPath();
    $path = str_replace(__DIR__ . '/', '', $path);
    $phar->addFile($file->getRealPath(), $path);
}


$phar->setStub(file_get_contents(__DIR__ . '/phar-stub.php'));
$phar->stopBuffering();
chmod($pharFile, 0755);
echo "PowerDI Phar created\n";