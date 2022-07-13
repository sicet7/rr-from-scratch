<?php

namespace Sicet7\Slim\Attributes;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\PropertyInjection\Interfaces\IgnoreAutoInjectionInterface;
use Sicet7\Slim\BaseMiddleware;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttributeMiddleware extends BaseMiddleware implements IgnoreAutoInjectionInterface
{
    /**
     * @var bool
     */
    private bool $injectionsRun = false;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle($request);
    }

    /**
     * Should return a unique string identifying the instance of the middleware with the specific combination of
     * constructor parameters, this is to try and share middleware instances across multiple actions to save memory.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * @return bool
     */
    public function isInjectionsRun(): bool
    {
        return $this->injectionsRun;
    }
}