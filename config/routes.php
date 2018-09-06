<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'Mailchimp',
    ['path' => '/mailchimp'],
    function (RouteBuilder $routes) {
        $routes->connect('/webhook', ['controller' => 'Webhook', 'action' => 'process']);
        $routes->fallbacks(DashedRoute::class);
    }
);
