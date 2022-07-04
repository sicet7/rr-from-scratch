<?php

use Sicet7\Container\ContainerBuilder;
use Sicet7\Container\Processors\DefinitionProcessor;
use Sicet7\Module\ModuleProcessor;
use Sicet7\PropertyInjection\InjectionProcessor;
use Sicet7\Slim\Processors\RouteAndMiddlewareProcessor;

/**
 * Processors registrations
 *
 * Processors are classes that process classes found in the sources.
 * Processors will return definitions to the container based on the classes that was found and reflection upon.
 */
ContainerBuilder::registerProcessor(DefinitionProcessor::class);
ContainerBuilder::registerProcessor(ModuleProcessor::class);
ContainerBuilder::registerProcessor(InjectionProcessor::class);
ContainerBuilder::registerProcessor(RouteAndMiddlewareProcessor::class);

/**
 * Source registrations
 *
 * Here you should register directories where you have Classes stored that the Processors should derive definitions from.
 */
ContainerBuilder::registerSource(__DIR__ . '/lib');
ContainerBuilder::registerSource(__DIR__ . '/app');