<?php

/**
 * This file is part of CakeDI.
 *
 ** (c) 2016 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\Cake\DI\Event;

use Cake\Event\Event;
use Interop\Container\ContainerInterface;

final class ConstructedEvent extends Event
{
    const EVENT = 'WyriHaximus.DI.container.constructed';

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function create(ContainerInterface $container)
    {
        return new static(static::EVENT, $container, [
            'container' => $container,
        ]);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->data()['container'];
    }
}