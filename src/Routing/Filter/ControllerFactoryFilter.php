<?php declare(strict_types=1);

namespace WyriHaximus\Cake\DI\Routing\Filter;

use Cake\Core\App;
use Cake\Routing\Filter\ControllerFactoryFilter as ParentFactory;
use Cake\Utility\Inflector;
use Doctrine\Common\Annotations\AnnotationReader;
use Interop\Container\ContainerInterface;
use WyriHaximus\Cake\DI\Annotations\Inject;

/**
 * A dispatcher filter that builds the controller to dispatch
 * in the request.
 *
 * This filter resolves the request parameters into a controller
 * instance and attaches it to the event object.
 */
class ControllerFactoryFilter extends ParentFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Priority is set high to allow other filters to be called first.
     *
     * @var int
     */
    protected $priority = 50;

    // @codingStandardsIgnoreStart
    protected function _getController($request, $response)
    {
        // @codingStandardsIgnoreEnd
        $pluginPath = $controller = null;
        $namespace = 'Controller';
        if (!empty($request->getParam('plugin'))) {
            $pluginPath = $request->getParam('plugin') . '.';
        }

        if (!empty($request->getParam('controller'))) {
            $controller = $request->getParam('controller');
        }
        if (!empty($request->getParam('prefix'))) {
            $namespace .= '/' . Inflector::camelize($request->getParam('prefix'));
        }
        $className = false;
        if ($pluginPath . $controller) {
            $className = App::classname($pluginPath . $controller, $namespace, 'Controller');
        }

        if (!$className) {
            return false;
        }

        $instance = new $className($request, $response);
        if (method_exists($instance, 'viewBuilder')) {
            $instance->viewBuilder();
        } else {
            $instance->viewPath = null;
        }

        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($className);

        foreach ($reflectionClass->getMethods() as $method) {
            if (!is_a($reader->getMethodAnnotation($method, Inject::class), Inject::class)) {
                continue;
            }

            $params = [];
            foreach ($method->getParameters() as $parameter) {
                $params[] = $this->container->get((string)$parameter->getType());
            }

            $method = $method->getName();

            $instance->$method(...$params);
        }

        return $instance;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
