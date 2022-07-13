<?php

namespace Sicet7\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieJar
{
    public const ATTRIBUTE_NAME = 'cookieJar';

    /**
     * @var Cookie[]
     */
    private array $cookies = [];

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws CookieException
     */
    public static function init(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(self::ATTRIBUTE_NAME, new CookieJar($request));
    }

    /**
     * @param ServerRequestInterface $request
     * @throws CookieException
     */
    private function __construct(ServerRequestInterface $request)
    {
        try {
            foreach ($request->getCookieParams() as $cookieName => $cookieValue) {
                $this->cookies[] = new Cookie($cookieName, $cookieValue, false);
            }
        } catch (\Throwable $exception) {
            throw new CookieException('Failed to boot CookieJar.', $exception->getCode(), $exception);
        }
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param string $name
     * @return Cookie|null
     */
    public function find(string $name): ?Cookie
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() == $name) {
                return $cookie;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Cookie
     * @throws CookieException
     */
    public function newCookie(string $name, string $value): Cookie
    {
        if ($this->find($name) !== null) {
            throw new CookieException('A cookie with the name "' . $name . '" already exists.');
        }
        try {
            $cookie = new Cookie($name, $value);
            $this->cookies[] = $cookie;
            return $cookie;
        } catch (\Throwable $exception) {
            throw new CookieException('Failed to create new cookie.', $exception->getCode(), $exception);
        }
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function applyToResponse(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->cookies as $cookie) {
            $response = $cookie->addToResponse($response);
        }
        return $response;
    }
}