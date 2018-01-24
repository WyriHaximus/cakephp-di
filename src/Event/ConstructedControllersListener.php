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

use Cake\Event\EventListenerInterface;
use function DI\factory;
use DI\Scope;
use Doctrine\Common\Annotations\AnnotationReader;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use WyriHaximus\Cake\DI\Annotations\Inject;

final class ConstructedControllersListener implements EventListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return array
     */
    public function implementedEvents()
    {
        return [
            ConstructedEvent::EVENT => 'constructed',
        ];
    }

    /**
     * @param ConstructedEvent $event
     */
    public function constructed(ConstructedEvent $event)
    {
        $this->container = $event->getContainer();

        $path = APP . 'Controller' . DS;
        $namespace = 'App\Controller';
        $directory = new RecursiveDirectoryIterator($path);
        $directory = new RecursiveIteratorIterator($directory);

        foreach ($directory as $node) {
            if (!is_file($node->getPathname())) {
                continue;
            }

            $file = substr($node->getPathname(), strlen($path));
            $file = ltrim($file, DIRECTORY_SEPARATOR);
            $file = rtrim($file, '.php');

            $class = $namespace . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $file);

            if (!class_exists($class)) {
                continue;
            }

            $this->shareController($class);
        }
    }

    protected function shareController(string $class)
    {
        $this->container->set($class, factory(function () use ($class) {
            $controller = new $class();

            $reader = new AnnotationReader();
            $reflectionClass = new ReflectionClass($class);

            foreach ($reflectionClass->getMethods() as $method) {
                if (!is_a($reader->getMethodAnnotation($method, Inject::class), Inject::class)) {
                    continue;
                }

                $params = [];
                foreach ($method->getParameters() as $parameter) {
                    $params[] = $this->container->get((string)$parameter->getType());
                }

                $method = $method->getName();

                $controller->$method(...$params);
            }

            return $controller;
        })->scope(Scope::PROTOTYPE));
    }
}