<?php

namespace Sicet7\Cookie;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Slim\BaseMiddleware;

class CookieJarMiddleware extends BaseMiddleware
{
    public const ATTRIBUTE_NAME = 'cookieJar';

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws CookieException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $cookieJar = new CookieJar($request, $this->container);
        $request = $request->withAttribute(self::ATTRIBUTE_NAME, $cookieJar);
        $response = $handler->handle($request);
        return $cookieJar->applyToResponse($response);
    }
}