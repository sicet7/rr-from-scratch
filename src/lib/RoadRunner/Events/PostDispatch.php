<?php

namespace Sicet7\RoadRunner\Events;

use Psr\Http\Message\ResponseInterface;

final class PostDispatch
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}