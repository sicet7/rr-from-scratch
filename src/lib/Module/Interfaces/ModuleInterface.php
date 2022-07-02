<?php

namespace Sicet7\Module\Interfaces;

use Psr\Container\ContainerInterface;

interface ModuleInterface
{
    /**
     * @return array
     */
    public static function getDefinitions(): array;

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function setup(ContainerInterface $container): void;
}