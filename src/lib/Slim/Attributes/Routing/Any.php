<?php

namespace Sicet7\Slim\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Any extends Route
{
    /**
     * Any constructor.
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern);
    }
}