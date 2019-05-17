<?php

use App\Classes\LendMNApi\CurlHttpClient;
use App\Classes\LendMNApi\MerchantApi;
use Illuminate\Database\Capsule\Manager;
use Slim\App;
use Slim\Container;

$app = new App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'demoapp',
            'username' => '',                           // demo app руу хандах username, заавал тохируулж өгөх шаардлагатай
            'password' => '',                           // demo app руу хандах password, заавал тохируулж өгөх шаардлагатай
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'clientId' => '',                               // мерчантын clientId, заавал тохируулж өгөх шаардлагатай
        'clientSecret' => '',                           // мерчантын clientSecret, заавал тохируулж өгөх шаардлагатай
        'apiBaseUrl' => 'https://mgw.test.lending.mn',
        'successUri' => '/order/success?order_id=',
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
$capsule = new Manager;
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

$container['api'] = function($container) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);

    // curl client
    $client = new CurlHttpClient($logger);

    // api client
    $api = new MerchantApi($container['settings']['apiBaseUrl'], $client);

    return $api;
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
