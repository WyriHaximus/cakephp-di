<?php

use Cake\Routing\DispatcherFactory;
use WyriHaximus\Cake\DI\Routing\Filter\ControllerFactoryFilter;

$container = require __DIR__ . DS . 'container.php';
$controllerFactory = new ControllerFactoryFilter();
$controllerFactory->setContainer($container);

/**
 * Connect middleware/dispatcher filters.
 */
DispatcherFactory::add('Asset');
DispatcherFactory::add('Routing');
DispatcherFactory::add($controllerFactory);
