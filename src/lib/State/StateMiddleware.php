<?php

namespace Sicet7\State;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Container\Attributes\Definition;
use Sicet7\Slim\BaseMiddleware;
use Sicet7\State\Interfaces\StateManagerInterface;

#[Definition]
class StateMiddleware extends BaseMiddleware
{
    public const ATTRIBUTE_NAME = 'state';

    /**
     * @var StateManagerInterface
     */
    private StateManagerInterface $stateManager;

    /**
     * @param StateManagerInterface $stateManager
     */
    public function __construct(StateManagerInterface $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $state = $this->stateManager->initState($request);
        $request = $request->withAttribute(self::ATTRIBUTE_NAME, $state);
        $response = $handler->handle($request);
        return $this->stateManager->persistState($state, $request, $response);
    }
}