<?php

namespace App\Http\Actions;

use App\Http\Middlewares\RequireQueryParam;
use App\Http\Transport\TestDTO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sicet7\PropertyInjection\Attributes\Inject;
use Sicet7\Slim\Attributes\FromBody;
use Sicet7\Slim\Attributes\Routing\Get;
use Sicet7\Slim\Interfaces\DTOInterface;

#[Get('/')]
#[RequireQueryParam('api-key', 'my-secret-key')]
class TestAction
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
     * @var ContainerInterface
     */
    #[Inject]
    private ContainerInterface $container;

    /**
     * @return ResponseInterface
     */
    public function __invoke(
        #[FromBody] TestDTO $dto
    ): ResponseInterface {
        return $this->responseFactory->createResponse()->withBody($this->streamFactory->createStream(
            var_export($dto->getParsedBody(), true)
        ));
    }
}