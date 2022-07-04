<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Module\Interfaces\ModuleInterface;
use Slim\App;
use function DI\decorate;

class AppModule implements ModuleInterface
{
    /**
     * @return array
     */
    public static function getDefinitions(): array
    {
        return [
            App::class => decorate(function (App $app, ContainerInterface $container): App {
                $app->addRoutingMiddleware();
                $logger = ($container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null);
                $app->addErrorMiddleware(true,true, true, $logger);
                return $app;
            }),
            Logger::class => decorate(function (Logger $logger, ContainerInterface $container): Logger {
                $logger->pushHandler(new StreamHandler(APP_ROOT . '/var/log/app.log', Level::Debug));
                return $logger;
            }),
        ];
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function setup(ContainerInterface $container): void
    {
        // Do nothing.... YET! :-)
    }
}