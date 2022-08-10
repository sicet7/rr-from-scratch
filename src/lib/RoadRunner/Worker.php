<?php

namespace Sicet7\RoadRunner;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Container\Attributes\Definition;
use Sicet7\RoadRunner\Events\BadRequest;
use Sicet7\RoadRunner\Events\Init;
use Sicet7\RoadRunner\Events\InternalServerError;
use Sicet7\RoadRunner\Events\PostDispatch;
use Sicet7\RoadRunner\Events\PreDispatch;
use Sicet7\RoadRunner\Events\TerminateWorker;
use Spiral\RoadRunner\Http\PSR7Worker;

#[Definition]
class Worker
{
    /**
     * @var bool
     */
    public bool $running = true;

    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var PSR7Worker
     */
    private PSR7Worker $PSR7Worker;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * @param RequestHandlerInterface $requestHandler
     * @param PSR7Worker $PSR7Worker
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface|null $logger
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        RequestHandlerInterface $requestHandler,
        PSR7Worker $PSR7Worker,
        ResponseFactoryInterface $responseFactory,
        ?LoggerInterface $logger = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->requestHandler = $requestHandler;
        $this->PSR7Worker = $PSR7Worker;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return void
     */
    public function init(): void
    {
        $this->eventDispatcher?->dispatch(new Init());
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->running = true;
        do {
            try {
                $request = $this->PSR7Worker->waitRequest();

                if (!($request instanceof ServerRequestInterface)) {
                    $this->logger?->info('Termination request received');
                    $this->eventDispatcher?->dispatch(new TerminateWorker());
                    break;
                }

            } catch (\Throwable $throwable) {
                $this->logger?->notice(
                    'Malformed request received!',
                    $this->throwableToArray($throwable)
                );
                try {
                    $this->eventDispatcher?->dispatch(new BadRequest($throwable));
                    $this->PSR7Worker->respond(
                        $this->responseFactory->createResponse(400, 'Bad Request')
                    );
                } catch (\Throwable $badRequestException) {
                    $this->logger?->error('Failed to deliver bad request response, terminating worker.',
                        $this->throwableToArray($badRequestException)
                    );
                    break;
                }
                continue;
            }

            try {
                $this->eventDispatcher?->dispatch(new PreDispatch($request));
                $response = $this->requestHandler->handle($request);
                $this->eventDispatcher?->dispatch(new PostDispatch($response));
                $this->PSR7Worker->respond($response);
            } catch (\Throwable $throwable) {
                $this->logger?->error(
                    'Request handler threw unhandled exception!',
                    $this->throwableToArray($throwable)
                );
                try {
                    $this->eventDispatcher?->dispatch(new InternalServerError($throwable));
                    $this->PSR7Worker->respond(
                        $this->responseFactory->createResponse(500, 'Internal Server Error')
                    );
                } catch (\Throwable $internalServerError) {
                    $this->logger?->error('Failed to deliver internal server error response, terminating worker.',
                        $this->throwableToArray($internalServerError)
                    );
                    break;
                }
            }

        } while($this->running);
    }

    /**
     * @param \Throwable $throwable
     * @return array
     */
    protected function throwableToArray(\Throwable $throwable): array
    {
        $output = [
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ];
        if ($throwable->getPrevious() instanceof \Throwable) {
            $output['previous'] = $this->throwableToArray($throwable->getPrevious());
        }
        return $output;
    }
}