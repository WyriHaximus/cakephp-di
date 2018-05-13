<?php declare(strict_types=1);

use Cake\Event\EventManager;
use DI\ContainerBuilder;
use PHPDIDefinitions\DefinitionsGatherer;
use WyriHaximus\Cake\DI\Event\ConstructedEvent;

return (function () {
    $container = (new ContainerBuilder())->addDefinitions(iterator_to_array(DefinitionsGatherer::gather()))->build();
    EventManager::instance()->dispatch(ConstructedEvent::create($container));
    return $container;
})();
