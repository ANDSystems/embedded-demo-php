# embedded-demo-php
LendMN Embedded demo app 


##### Систэмийн шаардлага
* PHP 7.1 эсвэл дээш хувилбар
* Apache эсвэл nginx веб сэрвэр

#### Хэрхэн суулгах вэ
Yндсэн фолдэрт дараах коммандыг терминал дээрээс ажилуулна.

	 composer install

#### Тохиргоо хийх

Дараах файлыг нээгээд ([bootstrap/app.php](bootstrap/app.php)) үндсэн тохиргоог хийнэ

    $app = new App([
        'settings' => [
            'displayErrorDetails' => true,
            'db' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'demoapp',
                'username' => '{{username}}',
                'password' => '{{password}}',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
            ],
            'clientId' => '{{clientId}}',
            'clientSecret' => '{{clientSecret}}',
            'apiBaseUrl' => 'https://mgw.test.lending.mn',
            'successUri' => '/order/success?order_id=',
            'redirectUri' => '/',
        ]
    ]);

**clientId** болон **clientSecret** кодыг таньд LendMN-ээс өгнө.


#### Датабайс үүсгэх

([data/demoapp.sql](data/demoapp.sql)) гэсэн **sql** файлыг импортлоно.


