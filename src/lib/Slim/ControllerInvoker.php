<?php

namespace Sicet7\Slim;

use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class ControllerInvoker implements InvocationStrategyInterface
{
    /**
     * @var InvokerInterface
     */
    private InvokerInterface $invoker;

    /**
     * @var array
     */
    private array $slimKnownActionsDtos = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->invoker = new Invoker(new ResolverChain([
            new AssociativeArrayResolver(),
            new TypeHintContainerResolver($container),
            new DefaultValueResolver()
        ]));
        if ($container->has('slim.known-actions.dtos')) {
            $this->slimKnownActionsDtos = $container->get('slim.known-actions.dtos');
        }
    }

    /**
     * @param callable $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $routeArguments
     * @return ResponseInterface
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        $parameters = [
            'request'  => $request,
            'response' => $response,
        ];
        $parameters += $routeArguments;
        $parameters += $request->getAttributes();
        if (is_object($callable) && array_key_exists(get_class($callable), $this->slimKnownActionsDtos)) {
            [$dtoFqcn, $parameterName] = $this->slimKnownActionsDtos[get_class($callable)];
            $parameters[$parameterName] = new $dtoFqcn($request->getParsedBody());
        }
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * @return InvokerInterface
     */
    public function getInvoker(): InvokerInterface
    {
        return $this->invoker;
    }

    /**
     * @param InvokerInterface $invoker
     */
    public function setInvoker(InvokerInterface $invoker): void
    {
        $this->invoker = $invoker;
    }
}