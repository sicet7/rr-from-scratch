<?php

namespace Sicet7\PropertyInjection;

use DI\Definition\Resolver\ObjectCreator;
use Psr\Container\ContainerInterface;
use Sicet7\Container\Interfaces\AttributeProcessorInterface;
use Sicet7\PropertyInjection\Attributes\Inject;
use Sicet7\PropertyInjection\Exceptions\PropertyInjectionException;
use function DI\decorate;

class InjectionProcessor implements AttributeProcessorInterface
{

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
        /* can only inject on classes with a definition in the container. */
        if (empty($class->getAttributes())) {
            return [];
        }

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

        if (empty($injectionPoints)) {
            return [];
        }

        $className = $class->getName();

        return [
            $className => decorate(function ($previous, ContainerInterface $container) use ($className, $injectionPoints) {
                foreach ($injectionPoints as $propertyName => $def) {
                    [$definitionName, $optional] = $def;
                    if ($optional === true && !$container->has($definitionName)) {
                        continue;
                    }
                    ObjectCreator::setPrivatePropertyValue(
                        $className,
                        $previous,
                        $propertyName,
                        $container->get($definitionName)
                    );
                }
                return $previous;
            }),
        ];
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
        return;
    }
}