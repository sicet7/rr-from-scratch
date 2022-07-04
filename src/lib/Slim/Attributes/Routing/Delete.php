<?php

namespace Sicet7\Slim\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Delete extends Route
{
    /**
     * Delete constructor.
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct(['DELETE'], $pattern);
    }
}