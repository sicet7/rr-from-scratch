<?php

use Sicet7\Container\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

const APP_ROOT = __DIR__;

$container = ContainerBuilder::build();

$worker = $container->get(\Sicet7\RoadRunner\Worker::class);
/** @var \Sicet7\RoadRunner\Worker $worker */

$worker->run();