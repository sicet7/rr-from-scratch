<?php

namespace Sicet7\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Slim\BaseMiddleware;

class CookieJarMiddleware extends BaseMiddleware
{
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
        $request = CookieJar::init($request);
        $cookieJar = $request->getAttribute(CookieJar::ATTRIBUTE_NAME);
        /** @var CookieJar|null $cookieJar */
        $response = $handler->handle($request);
        return $cookieJar?->applyToResponse($response) ?? $response;
    }
}