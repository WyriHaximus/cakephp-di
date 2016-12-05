<?php

use Cake\Core\Configure;
use DI\ContainerBuilder;

return (new ContainerBuilder())->addDefinitions(Configure::read('WyriHaximus.DI.definitions.path'))->build();
