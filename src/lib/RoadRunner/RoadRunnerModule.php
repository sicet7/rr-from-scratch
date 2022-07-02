<?php

namespace Sicet7\RoadRunner;

use Psr\Container\ContainerInterface;
use Sicet7\Module\Interfaces\ModuleInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Spiral\Goridge\Relay;
use Spiral\Goridge\RelayInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Metrics\Metrics;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;
use function DI\get;

class RoadRunnerModule implements ModuleInterface
{
    /**
     * @return array
     */
    public static function getDefinitions(): array
    {
        return [
            Environment::class => function (): Environment {
                return Environment::fromGlobals();
            },
            EnvironmentInterface::class => get(Environment::class),
            RelayInterface::class => function (EnvironmentInterface $environment): RelayInterface {
                return Relay::create($environment->getRelayAddress());
            },
            RPCInterface::class => function (EnvironmentInterface $environment): RPCInterface {
                return RPC::create($environment->getRPCAddress());
            },
            RoadRunnerWorker::class => function (RelayInterface $relay): RoadRunnerWorker
            {
                return new RoadRunnerWorker($relay);
            },
            PSR7Worker::class => function (
                RoadRunnerWorker $worker,
                ServerRequestFactoryInterface $requestFactory,
                StreamFactoryInterface $streamFactory,
                UploadedFileFactoryInterface $uploadedFileFactory
            ): PSR7Worker {
                return new PSR7Worker(
                    $worker,
                    $requestFactory,
                    $streamFactory,
                    $uploadedFileFactory
                );
            },
            Metrics::class => function (RPCInterface $RPC) {
                return new Metrics($RPC);
            },
        ];
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function setup(ContainerInterface $container): void
    {
        return;
    }
}