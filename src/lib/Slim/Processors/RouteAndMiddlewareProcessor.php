<?php

namespace Sicet7\Slim\Processors;

use DI\Definition\Resolver\ObjectCreator;
use Psr\Container\ContainerInterface;
use Sicet7\Container\Factories\DefaultDefinitionFactory;
use Sicet7\Container\Interfaces\AttributeProcessorInterface;
use Sicet7\PropertyInjection\InjectionProcessor;
use Sicet7\Slim\Attributes\FromBody;
use Sicet7\Slim\Attributes\AttributeMiddleware;
use Sicet7\Slim\Attributes\Routing\Route;
use Sicet7\Slim\Exceptions\ActionDefinitionException;
use Slim\Interfaces\RouteCollectorInterface;
use function DI\add;
use function DI\decorate;
use function DI\factory;

class RouteAndMiddlewareProcessor implements AttributeProcessorInterface
{
    /**
     * @var Route[]
     */
    private array $routeAttributes = [];

    /**
     * @var AttributeMiddleware[]
     */
    private array $middlewareInstances = [];

    /**
     * @var AttributeMiddleware[][]
     */
    private array $middlewareMappings = [];

    public function __construct()
    {
    }

    /**
     * @param \ReflectionClass $class
     * @return array
     * @throws ActionDefinitionException
     */
    public function getDefinitionsForClass(\ReflectionClass $class): array
    {
        if (empty($attributes = $class->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF))) {
            return [];
        }
        if (!$class->hasMethod('__invoke')) {
            throw new ActionDefinitionException(
                'Failed to define "' . $class->getName() . '" Action. Missing "__invoke" method.'
            );
        }
        $output = [
            $class->getName() => factory([DefaultDefinitionFactory::class, 'create']),
        ];
        $invokeMethod = $class->getMethod('__invoke');
        if (!empty($parameters = $invokeMethod->getParameters())) {
            foreach ($parameters as $parameter) {
                if (empty($fromBody = $parameter->getAttributes(FromBody::class, \ReflectionAttribute::IS_INSTANCEOF))) {
                    unset($fromBody);
                    continue;
                }
                $fromBody = $fromBody[array_key_first($fromBody)]->newInstance();
                /** @var FromBody $fromBody */
                if (
                    $fromBody->getDtoFqcn() === null && (
                        !($parameter->getType() instanceof \ReflectionNamedType) ||
                        ($parameter->getType() instanceof \ReflectionNamedType && $parameter->getType()->isBuiltin())
                    ) && class_exists($fromBody->getDtoFqcn() ?? $parameter->getType()->getName())
                ) {
                    throw new ActionDefinitionException(
                        'Could not resolve type for "FromBody" on parameter "' . $parameter->getName() . '" on method "' . $class->getName() . '::__invoke"'
                    );
                }
                $output['slim.known-actions.dtos'] = add([
                    $class->getName() => [$fromBody->getDtoFqcn() ?? $parameter->getType()->getName(), $parameter->getName()],
                ]);
                break;
            }
        }
        foreach ($attributes as $attribute) {
            $this->routeAttributes[$class->getName()] = $attribute->newInstance();
        }

        if (!empty($middlewares = $class->getAttributes(AttributeMiddleware::class, \ReflectionAttribute::IS_INSTANCEOF))) {
            foreach ($middlewares as $middleware) {
                $instance = $middleware->newInstance();
                /** @var AttributeMiddleware $instance */
                $identifier = $instance->getIdentifier();
                if (!array_key_exists($identifier, $this->middlewareInstances)) {
                    $this->middlewareInstances[$identifier] = $instance;
                }
                if (
                    !array_key_exists($class->getName(), $this->middlewareMappings) ||
                    !is_array($this->middlewareMappings[$class->getName()])
                ) {
                    $this->middlewareMappings[$class->getName()] = [];
                }
                $this->middlewareMappings[$class->getName()][] = $this->middlewareInstances[$identifier];
            }
        }

        return $output;
        //TODO: Handle Groups?? Maybe??.
    }

    /**
     * @return array
     */
    public function getInferredDefinitions(): array
    {
        $this->middlewareInstances = [];
        $routes = $this->routeAttributes;
        $middlewareMappings = $this->middlewareMappings;
        return [
            RouteCollectorInterface::class => decorate(function (
                RouteCollectorInterface $previous,
                ContainerInterface $container
            ) use ($routes, $middlewareMappings): RouteCollectorInterface {

                foreach ($routes as $actionFqcn => $routeAttribute) {
                    $route = $previous->map(
                        $routeAttribute->getMethods(),
                        $routeAttribute->getPattern(),
                        $actionFqcn
                    );
                    if (
                        array_key_exists($actionFqcn, $middlewareMappings) &&
                        !empty($middlewareMappings[$actionFqcn])
                    ) {
                        foreach ($middlewareMappings[$actionFqcn] as $middleware) {
                            if (!$middleware->isInjectionsRun()) {
                                InjectionProcessor::injectPropertiesOnObjectFromContainer(
                                    $middleware,
                                    $container
                                );
                                ObjectCreator::setPrivatePropertyValue(
                                    AttributeMiddleware::class,
                                    $middleware,
                                    'injectionsRun',
                                    true
                                );
                            }
                            $route->addMiddleware($middleware);
                        }
                    }
                }

                return $previous;
            }),
        ];
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function containerSetup(ContainerInterface $container): void
    {
        return;
    }
}