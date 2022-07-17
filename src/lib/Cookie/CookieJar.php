<?php

namespace Sicet7\Cookie;

use DI\FactoryInterface;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sicet7\Interfaces\StringableInterface;

class CookieJar
{
    public const MAPPING_KEY = 'cookieJar.mappings';

    /**
     * @var string[]
     */
    private array $cookieNames = [];

    /**
     * @var Cookie[]
     */
    private array $cookies = [];

    /**
     * @param ServerRequestInterface $request
     * @param ContainerInterface|null $container
     * @throws CookieException
     */
    public function __construct(ServerRequestInterface $request, ?ContainerInterface $container = null)
    {
        try {
            $mappings = (($container?->has(self::MAPPING_KEY) ?? false) ? $container->get(self::MAPPING_KEY) : []);
            $factory = $container->get(FactoryInterface::class);
            foreach ($request->getCookieParams() as $cookieName => $cookieValue) {
                $this->cookieNames[] = $cookieName;
                $value = $cookieValue;
                if (array_key_exists($cookieName, $mappings)) {
                    $value = $factory->make($mappings[$cookieName], [
                        'value' => $value
                    ]);
                }
                $this->cookies[] = new Cookie($cookieName, $value, false);
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
     * @param string|StringableInterface $value
     * @return Cookie
     * @throws CookieException
     */
    public function create(string $name, string|StringableInterface $value): Cookie
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
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        foreach ($this->cookies as $index => $cookie) {
            if ($cookie->getName() == $name) {
                unset($this->cookies[$index]);
                break;
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws CookieException
     */
    public function applyToResponse(ResponseInterface $response): ResponseInterface
    {
        $currentNames = [];
        foreach ($this->cookies as $cookie) {
            $currentNames[] = $cookie->getName();
            $response = $cookie->addToResponse($response);
        }
        $deletedCookies = array_filter($this->cookieNames, function ($v) use ($currentNames) {
            return !in_array($v, $currentNames);
        });
        foreach ($deletedCookies as $deletedCookie) {
            $response = SetCookie::thatDeletesCookie($deletedCookie)->addToResponse($response);
        }
        return $response;
    }
}