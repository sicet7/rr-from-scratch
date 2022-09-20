<?php

namespace Sicet7\State\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface StateManagerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return StateInterface
     */
    public function initState(ServerRequestInterface $request): StateInterface;

    /**
     * @param StateInterface $state
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function persistState(
        StateInterface $state,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;
}