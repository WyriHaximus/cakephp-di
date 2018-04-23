<?php declare(strict_types=1);

namespace WyriHaximus\Cake\DI\Http;

use Cake\Core\App;
use Cake\Http\ControllerFactory as ParentFactory;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
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
class ControllerFactory extends ParentFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?? require dirname(dirname(__DIR__)) . DS . 'config' . DS . 'container.php';
    }

    // @codingStandardsIgnoreStart
    public function create(ServerRequest $request, Response $response)
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
            $this->missingController($request);
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
}
