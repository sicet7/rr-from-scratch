<?php

namespace App\Modules;

use Defuse\Crypto\Key;
use Psr\Container\ContainerInterface;
use Sicet7\Module\Interfaces\ModuleInterface;
use function DI\env;

class OAuthModule implements ModuleInterface
{
    /**
     * @return array
     */
    public static function getDefinitions(): array
    {
        return [
            'oauth.encryption.key' => env('OAUTH_ENCRYPTION_KEY'),
            Key::class => function (ContainerInterface $container){
                return Key::loadFromAsciiSafeString($container->get('oauth.encryption.key'));
            },
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