<?php

use Composer\Autoload\ClassLoader;

$baseDir = dirname(__DIR__);

putenv('APP_BASE_PATH=' . $baseDir);
$_ENV['APP_BASE_PATH'] = $baseDir;
$_SERVER['APP_BASE_PATH'] = $baseDir;

/** @var ClassLoader $loader */
$loader = require $baseDir . '/vendor/autoload.php';

$loader->setPsr4('App\\', [$baseDir . '/app']);
$loader->setPsr4('Tests\\', [$baseDir . '/tests']);
$loader->setPsr4('Database\\Factories\\', [$baseDir . '/database/factories']);
$loader->setPsr4('Database\\Seeders\\', [$baseDir . '/database/seeders']);

\Closure::bind(function () {
    foreach (array_keys($this->classMap) as $class) {
        if (str_starts_with($class, 'App\\') || str_starts_with($class, 'Tests\\') || str_starts_with($class, 'Database\\')) {
            unset($this->classMap[$class]);
        }
    }
}, $loader, ClassLoader::class)();

return $loader;
