<?php

namespace App;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Container\Attributes\Definition;
use Sicet7\PropertyInjection\Attributes\Inject;
use Spiral\RoadRunner\Metrics\Metrics;

#[Definition([RequestHandlerInterface::class])]
class TestRequestHandler implements RequestHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    #[Inject]
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    #[Inject]
    private StreamFactoryInterface $streamFactory;

    /**
     * @var Metrics
     */
    #[Inject]
    private Metrics $metrics;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->metrics->add('app_test_counter', 1.2, [ "my-type" ]);
        return $this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('Hello World'));
    }
}