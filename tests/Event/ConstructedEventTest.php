<?php declare(strict_types=1);

/**
 * This file is part of CakeDI.
 *
 ** (c) 2016 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\Tests\Cake\DI\Event;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use WyriHaximus\Cake\DI\Event\ConstructedEvent;

final class ConstructedEventTest extends TestCase
{
    public function testEvent()
    {
        $container = ContainerBuilder::buildDevContainer();

        $event = ConstructedEvent::create($container);

        $this->assertSame($container, $event->getContainer());
    }
}
