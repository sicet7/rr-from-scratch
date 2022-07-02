<?php

namespace Sicet7\Container;

use DI\ContainerBuilder as DIContainerBuilder;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Sicet7\Container\Exceptions\ContainerBuilderException;
use Sicet7\Container\Interfaces\AttributeProcessorInterface;

class ContainerBuilder
{
    /**
     * @var string[]
     */
    private static array $directories = [];

    /**
     * @var string[]
     */
    private static array $processors = [];

    /**
     * @var bool
     */
    protected static bool $annotations = false;

    /**
     * @var bool
     */
    protected static bool $autowiring = false;

    /**
     * @param bool $value
     * @return void
     */
    public static function enableAnnotations(bool $value = true): void
    {
        self::$annotations = $value;
    }

    /**
     * @param bool $value
     * @return void
     */
    public static function enableAutowiring(bool $value = true): void
    {
        self::$autowiring = $value;
    }

    /**
     * @param string $processorFqcn
     * @return void
     */
    public static function registerProcessor(string $processorFqcn): void
    {
        if (!in_array($processorFqcn, self::$processors)) {
            self::$processors[] = $processorFqcn;
        }
    }

    /**
     * @param string $directory
     * @return void
     * @throws ContainerBuilderException
     */
    public static function registerSource(string $directory): void
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new ContainerBuilderException('Failed to find directory: "' . $directory . '".');
        }
        if (!in_array($directory, self::$directories)) {
            self::$directories[] = $directory;
        }
    }

    /**
     * @param array $additionalDefinitions
     * @return ContainerInterface
     * @throws \Exception
     */
    public static function build(array $additionalDefinitions = []): ContainerInterface
    {
        $builder = new DIContainerBuilder();
        $builder->useAnnotations(self::$annotations);
        $builder->useAutowiring(self::$autowiring);

        /** @var AttributeProcessorInterface[] $processors */
        $processors = [];
        foreach (self::$processors as $processor) {
            if (!class_exists($processor)) {
                continue;
            }
            $processors[] = new $processor;
        }

        $sourceLocator = new DirectoriesSourceLocator(
            self::$directories,
            (new BetterReflection())->astLocator()
        );

        $reflector = new DefaultReflector($sourceLocator);

        foreach ($reflector->reflectAllClasses() as $foundClass) {
            $reflection = new \ReflectionClass($foundClass->getName());
            if (empty($reflection->getAttributes()) && empty($reflection->getInterfaces())) {
                continue;
            }
            foreach ($processors as $processor) {
                $defs = $processor->getDefinitionsForClass($reflection);
                if (!empty($defs)) {
                    $builder->addDefinitions($defs);
                }
            }
        }
        unset($defs);

        foreach ($processors as $processor) {
            $defs = $processor->getInferredDefinitions();
            if (!empty($defs)) {
                $builder->addDefinitions($defs);
            }
        }

        unset($defs);

        $builder->addDefinitions($additionalDefinitions);

        $container = $builder->build();

        foreach ($processors as $processor) {
            $processor->containerSetup($container);
        }

        // Garbage collecting before returning.
        unset($builder, $processors, $reflector, $sourceLocator);
        gc_collect_cycles();
        return $container;
    }
}