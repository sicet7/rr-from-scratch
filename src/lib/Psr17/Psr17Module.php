<?php

namespace Sicet7\Psr17;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Sicet7\Module\Interfaces\ModuleInterface;

use function DI\create;
use function DI\get;

class Psr17Module implements ModuleInterface
{

    /**
     * @return array
     */
    public static function getDefinitions(): array
    {
        return [
            Psr17Factory::class => create(Psr17Factory::class),
            RequestFactoryInterface::class => get(Psr17Factory::class),
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => get(Psr17Factory::class),
            StreamFactoryInterface::class => get(Psr17Factory::class),
            UploadedFileFactoryInterface::class => get(Psr17Factory::class),
            UriFactoryInterface::class => get(Psr17Factory::class),
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