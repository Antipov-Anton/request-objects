<?php

namespace Fesor\RequestObject\Bundle;

use Fesor\RequestObject\Request;
use Fesor\RequestObject\RequestBinder;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class RequestObjectEventListener
{
    private $requestBinder;

    /**
     * RequestObjectEventListener constructor.
     * @param RequestBinder $requestBinder
     */
    public function __construct(RequestBinder $requestBinder)
    {
        $this->requestBinder = $requestBinder;
    }

    public function onKernelController(FilterControllerEvent $event)
    {

        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $request = $event->getRequest();
        $controllerReflection = new \ReflectionClass($controller[0]);
        $actionReflection = $controllerReflection->getMethod($controller[1]);
        $arguments = $actionReflection->getParameters();

        foreach ($arguments as $argument) {
            if (!($className = $argument->getClass())) {
                continue;
            }
            $className = $className->getName();
            
            $parents = class_parents($className);
            if (!in_array(Request::class, $parents)) {
                continue;
            }

            $requestObject = $this->requestBinder->bind($className, $request);
            $request->attributes->set($argument->getName(), $requestObject);
        }
    }

}