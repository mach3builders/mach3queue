<?php

use Mach3queue\Dashboard\Dashboard;

$bootstrap = './vendor/autoload.php';

if (!(file_exists($bootstrap) && include $bootstrap)) {
    fwrite(STDERR, 'Failed to load bootstrap.'.PHP_EOL);
    exit(1);
}

echo Dashboard::parse();