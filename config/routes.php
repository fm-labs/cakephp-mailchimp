<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin(
    'Mailchimp',
    ['path' => '/mailchimp'],
    function (RouteBuilder $routes) {
        $routes->connect('/webhook', ['controller' => 'Webhook', 'action' => 'process']);
        $routes->fallbacks(DashedRoute::class);
    }
);
