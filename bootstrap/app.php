<?php


use Slim\App;
use Slim\Container;

$app = new App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'demoapp',
            'username' => 'root',
            'password' => 'rootpass',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'clientId' => '42_6d4m6d8kqjggkkocs4o80gw84sgocskk8s4ckcw0k4ogcgwscw',
        'clientSecret' => 'ebv097t2lhwsgscs84kkk8oss4w084o8cg088cwgw40c0sc08',
        'apiBaseUrl' => 'https://mgw.test.lending.mn',
        'successUri' => '/order/success?order_id=',
        'redirectUri' => '/',
        'dataDir' => __DIR__ . '/../data',
    ]
]);


$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    return $view;
};

//boot eloquent connection
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
//pass the connection to global container (created in previous article)
$container['db'] = function (Container $container) use ($capsule){
    return $capsule;
};
// session
$container['session'] = function (Container $container) {
    return new Session();
};

//
$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};
$container['CartController'] = function ($container) {
    return new \App\Controllers\CartController($container);
};
$container['WebhookController'] = function ($container) {
    return new \App\Controllers\WebhookController($container);
};
$container['OrderController'] = function ($container) {
    return new \App\Controllers\OrderController($container);
};


// include routes
require __DIR__ . '/../app/routes.php';
