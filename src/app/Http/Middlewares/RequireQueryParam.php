<?php

namespace App\Http\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\PropertyInjection\Attributes\Inject;
use Sicet7\Slim\Attributes\AttributeMiddleware;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RequireQueryParam extends AttributeMiddleware
{
    /**
     * @var string
     */
    private string $queryParamName;

    /**
     * @var string|null
     */
    private ?string $value;

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
     * @param string $queryParamName
     * @param string|null $value
     */
    public function __construct(string $queryParamName, ?string $value = null)
    {
        $this->queryParamName = $queryParamName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return md5($this->queryParamName . ($this->value === null ? '' : ':' . $this->value));
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (
            empty($request->getQueryParams()) ||
            !array_key_exists($this->queryParamName, $request->getQueryParams())
        ) {
            return $this->responseFactory->createResponse(400, 'Bad Request')
                ->withBody($this->streamFactory->createStream('Missing "' . $this->queryParamName . '" query parameter'));
        }

        if ($this->value !== null && $request->getQueryParams()[$this->queryParamName] != $this->value) {
            return $this->responseFactory->createResponse(400, 'Bad Request')
                ->withBody($this->streamFactory->createStream('Invalid value for "' . $this->queryParamName . '" query parameter'));
        }

        return parent::process($request, $handler);
    }
}