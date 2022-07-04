<?php

namespace Sicet7\Slim\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    /**
     * @var array
     */
    private array $methods;

    /**
     * @var string
     */
    private string $pattern;

    /**
     * Route constructor.
     * @param array $methods
     * @param string $pattern
     */
    public function __construct(
        array $methods,
        string $pattern
    ) {
        $this->methods = $methods;
        $this->pattern = $pattern;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
}