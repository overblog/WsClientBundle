<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/ClassLoader.php';

use Symfony\Component\ClassLoader\ClassLoader;

$loader = new ClassLoader();
$loader->addPrefix('Symfony', $_SERVER['SYMFONY']);
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Overblog\\WsClientBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});