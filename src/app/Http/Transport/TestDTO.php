<?php

namespace App\Http\Transport;

use Sicet7\Slim\Interfaces\DTOInterface;

class TestDTO implements DTOInterface
{
    private array|null|object $parsedBody;

    /**
     * @param object|array|null $parsedBody
     */
    public function __construct(object|array|null $parsedBody)
    {
        $this->parsedBody = $parsedBody;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody(): object|array|null
    {
        return $this->parsedBody;
    }
}