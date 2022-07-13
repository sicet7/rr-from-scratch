<?php

namespace Sicet7\Cookie;

use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;

class Cookie
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $value;

    /**
     * @var int
     */
    private int $expiresAt = 0;

    /**
     * @var string
     */
    private string $path = '';

    /**
     * @var string
     */
    private string $domain = '';

    /**
     * @var bool
     */
    private bool $secure = false;

    /**
     * @var bool
     */
    private bool $httpOnly = false;

    /**
     * @var string
     */
    private string $sameSite = '';

    /**
     * @var SetCookie|null
     */
    private ?SetCookie $setCookie = null;

    /**
     * @param string $name
     * @param string $value
     * @param bool $isNew
     * @throws CookieException
     */
    public function __construct(string $name, string $value, bool $isNew = true)
    {
        $this->name = $name;
        $this->value = $value;
        if ($isNew) {
            $this->setCookie();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @throws CookieException
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->setCookie();
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @throws CookieException
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
        $this->setCookie();
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * @param int $expiresAt
     * @throws CookieException
     */
    public function setExpiresAt(int $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
        $this->setCookie();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @throws CookieException
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
        $this->setCookie();
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @throws CookieException
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
        $this->setCookie();
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     * @throws CookieException
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
        $this->setCookie();
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param bool $httpOnly
     * @throws CookieException
     */
    public function setHttpOnly(bool $httpOnly): void
    {
        $this->httpOnly = $httpOnly;
        $this->setCookie();
    }

    /**
     * @return string
     */
    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * @param string $sameSite
     * @throws CookieException
     */
    public function setSameSite(string $sameSite): void
    {
        $this->sameSite = $sameSite;
        $this->setCookie();
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @internal
     */
    public function addToResponse(ResponseInterface $response): ResponseInterface
    {
        if ($this->setCookie !== null) {
            $response = $this->setCookie->addToResponse($response);
        }
        return $response;
    }

    /**
     * @return void
     * @throws CookieException
     */
    private function setCookie(): void
    {
        try {
            $this->setCookie = new SetCookie(
                $this->getName(),
                $this->getValue(),
                $this->getExpiresAt(),
                $this->getPath(),
                $this->getDomain(),
                $this->isSecure(),
                $this->isHttpOnly(),
                $this->getSameSite()
            );
        } catch (\Throwable $exception) {
            throw new CookieException('Failed to prepare cookie header.', $exception->getCode(), $exception);
        }
    }
}