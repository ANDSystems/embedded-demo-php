<?php


// homepage
$app->get('/', 'HomeController:index');

// cart
$app->get('/cart/add', 'CartController:add');
$app->get('/cart/remove', 'CartController:remove');
$app->get('/cart/list', 'CartController:list');
$app->get('/cart/checkout', 'CartController:checkout');

// order
$app->get('/order/create', 'OrderController:create');
$app->get('/order/success', 'OrderController:success');
$app->get('/order/list', 'OrderController:list');


// lend webhook
$app->get('/webhook', 'WebhookController:index');
