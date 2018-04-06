<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

function checkRequirements() {
    if (version_compare(PHP_VERSION, '7.1.0', '<')) {
        throw new \RuntimeException('You need PHP 7.1 to run this application');
    }
}

checkRequirements();
