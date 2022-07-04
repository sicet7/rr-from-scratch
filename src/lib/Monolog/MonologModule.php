<?php

namespace Sicet7\Monolog;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Module\Interfaces\ModuleInterface;
use function DI\create;
use function DI\get;

class MonologModule implements ModuleInterface
{
    /**
     * @return array
     */
    public static function getDefinitions(): array
    {
        return [
            Logger::class => create(Logger::class)
                ->constructor('main'),
            LoggerInterface::class => get(Logger::class),
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