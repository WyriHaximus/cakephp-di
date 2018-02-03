<?php declare(strict_types=1);

namespace WyriHaximus\Cake\DI\Routing\Filter;

use Cake\Core\App;
use Cake\Utility\Inflector;
use Cake\Routing\Filter\ControllerFactoryFilter as ParentFactory;
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

    /**
     * Get controller to use, either plugin controller or application controller
     *
     * @param \Cake\Network\Request $request Request object
     * @param \Cake\Network\Response $response Response for the controller.
     * @return mixed name of controller if not loaded, or object if loaded
     */
    // @codingStandardsIgnoreStart
    protected function _getController($request, $response)
    {
        // @codingStandardsIgnoreEnd
        $pluginPath = $controller = null;
        $namespace = 'Controller';
        if (!empty($request->params['plugin'])) {
            $pluginPath = $request->params['plugin'] . '.';
        }

        if (!empty($request->params['controller'])) {
            $controller = $request->params['controller'];
        }
        if (!empty($request->params['prefix'])) {
            $namespace .= '/' . Inflector::camelize($request->params['prefix']);
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
