<?php

namespace Sicet7\Slim\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Options extends Route
{
    /**
     * Options constructor.
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct(['OPTIONS'], $pattern);
    }
}