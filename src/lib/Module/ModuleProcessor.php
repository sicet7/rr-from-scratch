<?php

namespace Sicet7\Module;

use Psr\Container\ContainerInterface;
use Sicet7\Container\Interfaces\AttributeProcessorInterface;
use Sicet7\Module\Interfaces\ModuleInterface;

class ModuleProcessor implements AttributeProcessorInterface
{
    /**
     * @var callable[]|Closure[]
     */
    private array $setupCallables = [];

    public function __construct()
    {
    }

    /**
     * @param \ReflectionClass $class
     * @return array
     */
    public function getDefinitionsForClass(\ReflectionClass $class): array
    {
        if (!$class->implementsInterface(ModuleInterface::class)) {
            return [];
        }

        $this->setupCallables[] = $class->getMethod('setup')->getClosure();

        return $class->getMethod('getDefinitions')->invoke(null);
    }

    /**
     * @return array
     */
    public function getInferredDefinitions(): array
    {
        return [];
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function containerSetup(ContainerInterface $container): void
    {
        foreach ($this->setupCallables as $callable) {
            $callable($container);
        }
        return;
    }
}