<?php

namespace Sicet7\Cookie;

use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Sicet7\Interfaces\StringableInterface;

class Cookie
{
    /**
     * @var string
     */
    private string $originalValue;

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
     * @var SameSite
     */
    private SameSite $sameSite = SameSite::EMPTY;

    /**
     * @var bool
     */
    private bool $hasChanged = false;

    /**
     * @param string $name
     * @param string|StringableInterface $value
     * @param bool $isNew
     */
    public function __construct(
        private string $name,
        private string|StringableInterface $value,
        bool $isNew = true
    ) {
        $this->originalValue = ($value instanceof StringableInterface ? $value->toString() : $value);
        if ($isNew) {
            $this->hasChanged = true;
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
     */
    public function setName(string $name): void
    {
        if ($this->name != $name) {
            $this->hasChanged = true;
        }
        $this->name = $name;
    }

    /**
     * @return string|StringableInterface
     */
    public function getValue(): string|StringableInterface
    {
        return $this->value;
    }

    /**
     * @param string|StringableInterface $value
     */
    public function setValue(string|StringableInterface $value): void
    {
        if ((is_string($value) && $this->value != $value)) {
            $this->hasChanged = true;
        }
        $this->value = $value;
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
     */
    public function setExpiresAt(int $expiresAt): void
    {
        if ($this->expiresAt != $expiresAt) {
            $this->hasChanged = true;
        }
        $this->expiresAt = $expiresAt;
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
     */
    public function setPath(string $path): void
    {
        if ($this->path != $path) {
            $this->hasChanged = true;
        }
        $this->path = $path;
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
     */
    public function setDomain(string $domain): void
    {
        if ($this->domain != $domain) {
            $this->hasChanged = true;
        }
        $this->domain = $domain;
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
     */
    public function setSecure(bool $secure): void
    {
        if ($this->secure != $secure) {
            $this->hasChanged = true;
        }
        $this->secure = $secure;
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
     */
    public function setHttpOnly(bool $httpOnly): void
    {
        if ($this->httpOnly != $httpOnly) {
            $this->hasChanged = true;
        }
        $this->httpOnly = $httpOnly;
    }

    /**
     * @return SameSite
     */
    public function getSameSite(): SameSite
    {
        return $this->sameSite;
    }

    /**
     * @param SameSite $sameSite
     */
    public function setSameSite(SameSite $sameSite): void
    {
        if ($sameSite->value != $this->getSameSite()->value) {
            $this->hasChanged = true;
        }
        $this->sameSite = $sameSite;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws CookieException
     * @internal
     */
    public function addToResponse(ResponseInterface $response): ResponseInterface
    {
        if ($this->hasChanged || $this->originalValue != $this->readValue()) {
            $response = $this->makeSetCookie()->addToResponse($response);
        }
        return $response;
    }

    /**
     * @return string
     */
    private function readValue(): string
    {
        $value = $this->getValue();
        if ($value instanceof StringableInterface) {
            $value = $value->toString();
        }
        return $value;
    }

    /**
     * @return SetCookie
     * @throws CookieException
     */
    private function makeSetCookie(): SetCookie
    {
        try {
            return new SetCookie(
                $this->getName(),
                $this->readValue(),
                $this->getExpiresAt(),
                $this->getPath(),
                $this->getDomain(),
                $this->isSecure(),
                $this->isHttpOnly(),
                $this->getSameSite()->value
            );
        } catch (\Throwable $exception) {
            throw new CookieException('Failed to prepare cookie header.', $exception->getCode(), $exception);
        }
    }
}