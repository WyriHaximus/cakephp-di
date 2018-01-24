<?php declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Event\EventManager;
use DI\ContainerBuilder;
use WyriHaximus\Cake\DI\Event\ConstructedEvent;

return (function () {
    $container = (new ContainerBuilder())->addDefinitions(Configure::read('WyriHaximus.DI.definitions.path'))->build();
    EventManager::instance()->dispatch(ConstructedEvent::create($container));
    return $container;
})();
