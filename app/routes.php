<?php


//$app->get('/home', function ($request, $response) {
//    return $this->view->render($response, 'home.twig');
//});

//
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


// lend mn web hook
$app->get('/webhook', 'WebhookController:index');



//$app->get('/get', 'HomeController:getall');