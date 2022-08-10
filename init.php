<?php

use Sicet7\Container\ContainerBuilder;

const APP_ROOT = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';

$container = ContainerBuilder::build();

$worker = $container->get(\Sicet7\RoadRunner\Worker::class);
/** @var \Sicet7\RoadRunner\Worker $worker */

$worker->init();