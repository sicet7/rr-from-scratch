<?php

namespace Sicet7\PropertyInjection;

use DI\Definition\Resolver\ObjectCreator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sicet7\Container\Interfaces\AttributeProcessorInterface;
use Sicet7\PropertyInjection\Attributes\Inject;
use Sicet7\PropertyInjection\Exceptions\PropertyInjectionException;
use Sicet7\PropertyInjection\Interfaces\IgnoreAutoInjectionInterface;
use function DI\decorate;

class InjectionProcessor implements AttributeProcessorInterface
{
    /**
     * @var array
     */
    private array $injectionsCollection = [];

    /**
     * @param \ReflectionClass $class
     * @return array<string, array>
     * @throws PropertyInjectionException
     */
    public static function getInjectionPoints(\ReflectionClass $class): array
    {
        if (empty($properties = $class->getProperties())) {
            return [];
        }

        $injectionPoints = [];

        foreach ($properties as $property) {
            if (empty($attributes = $property->getAttributes(Inject::class, \ReflectionAttribute::IS_INSTANCEOF))) {
                unset($attributes);
                continue;
            }

            foreach ($attributes as $attribute) {
                if (!(($instance = $attribute->newInstance()) instanceof Inject)) {
                    unset($instance);
                    continue;
                }
                /** @var Inject $instance */
                if (!empty($instance->getDefinitionName())) {
                    $injectionPoints[$property->getName()] = [
                        $instance->getDefinitionName(),
                        $property->getType()?->allowsNull() ?? false
                    ];
                    unset($instance);
                    break;
                }

                if (empty($type = $property->getType())) {
                    unset($instance, $type);
                    break;
                }

                if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
                    throw new PropertyInjectionException(
                        'Property injection failed for property "' . $class->getName() . '::' . $property->getName() . '". ' .
                        'UnionTypes and IntersectionTypes cannot be injected.'
                    );
                }

                if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                    throw new PropertyInjectionException(
                        'Property injection failed for property "' . $class->getName() . '::' . $property->getName() . '". ' .
                        'No definition specified in inject attribute.'
                    );
                }

                $injectionPoints[$property->getName()] = [
                    $property->getType()->getName(),
                    $property->getType()?->allowsNull() ?? false
                ];
                break;
            }
        }
        return $injectionPoints;
    }

    /**
     * @param object $target
     * @param ContainerInterface $container
     * @return void
     * @throws PropertyInjectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function injectPropertiesOnObjectFromContainer(
        object $target,
        ContainerInterface $container
    ): void {
        $reflection = new \ReflectionClass($target);
        $injectionPoints = self::getInjectionPoints($reflection);
        foreach ($injectionPoints as $propertyName => $def) {
            [$definitionName, $optional] = $def;
            if ($optional === true && !$container->has($definitionName)) {
                continue;
            }
            ObjectCreator::setPrivatePropertyValue(
                null,
                $target,
                $propertyName,
                $container->get($definitionName)
            );
        }
    }

    public function __construct()
    {
    }

    /**
     * @param \ReflectionClass $class
     * @return array
     * @throws PropertyInjectionException
     */
    public function getDefinitionsForClass(\ReflectionClass $class): array
    {
        if ($class->implementsInterface(IgnoreAutoInjectionInterface::class)) {
            return [];
        }

        $injectionPoints = self::getInjectionPoints($class);

        if (empty($injectionPoints)) {
            return [];
        }

        $this->injectionsCollection[$class->getName()] = $injectionPoints;
        return [];
    }

    /**
     * @return array
     */
    public function getInferredDefinitions(): array
    {
        $output = [];
        foreach ($this->injectionsCollection as $classFqcn => $injectionPoints) {
            $output[$classFqcn] = decorate(function ($previous, ContainerInterface $container) use ($classFqcn, $injectionPoints) {
                foreach ($injectionPoints as $propertyName => $def) {
                    [$definitionName, $optional] = $def;
                    if ($optional === true && !$container->has($definitionName)) {
                        continue;
                    }
                    ObjectCreator::setPrivatePropertyValue(
                        $classFqcn,
                        $previous,
                        $propertyName,
                        $container->get($definitionName)
                    );
                }
                return $previous;
            });
        }
        return $output;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function containerSetup(ContainerInterface $container): void
    {
        return;
    }
}