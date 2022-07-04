<?php

namespace Sicet7\Slim\Interfaces;

interface DTOInterface
{
    /**
     * @param array|object|null $parsedBody
     */
    public function __construct(array|object|null $parsedBody);
}