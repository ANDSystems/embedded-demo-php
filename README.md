# embedded-demo-php
LendMN Embedded demo app 


##### Систэмийн шаардлага
* PHP 7.1 эсвэл дээш хувилбар
* Apache эсвэл nginx веб сэрвэр

#### Хэрхэн суулгах вэ
Yндсэн фолдэрт дараах коммандыг терминал дээрээс ажилуулна.

```sh
composer install
```
#### Тохиргоо хийх

Дараах ([bootstrap/app.php](bootstrap/app.php)) файлыг нээгээд  үндсэн тохиргоог хийнэ

```php
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
```

**clientId** болон **clientSecret** кодыг таньд LendMN-ээс өгнө.


#### Датабайс үүсгэх

[data/sql/demoapp.sql](data/sql/demoapp.sql) гэсэн **sql** файлыг импортлоно.

---

#### Сэрвер талын кодчлол

[app/Classes/LendMNApi](app/Classes/LendMNApi) 

Yндсэн бүтэц

    app
        Classes
        Controllers
        Models
    bootstrap
    data
    public
    resources

* **app** - фолдэрт вэбийн үндсэн код байршина
    * **Classes** - Merchant api дуудах класс
    * **Controllers** - контроллэр класс
    * **Model** - модел класс
* **bootstrap** - үндсэн тохиргоо агуулагдана
* **data** - дотор [public.pem](data/certs/public.pem) болон [demoapp.sql](data/sql/demoapp.sql)
* **public** - вэб хуудасны үндсэн фолдэр. [index.php](public/index.php) 
* **resources** - темплайт файлууд байршина.

---     

##### 1. Api Code жишээ
Merchant сэрвис дуудах жишээ код [app/Classes/LendMNApi/MerchantApi.php](app/Classes/LendMNApi/MerchantApi.php)
файл дотор байгаа болно.


##### 2. Access Token авах  
LendMN app ашиглаж Embedded App нээх үед, хэрэглэгчийн мэдээлэл авах анхааруулга гарч ирэх бөгөөд, хэрэглэгч
зөвшөөрсөн тохиолдолд, таны вэб хуудсыг **code** гэсэн параметр дамжуулж ачааллуулна.  

Ирсэн **code** параметер ашиглаж хэрэглэгчийн **access token** авах боломжтой. 

Жишээ код: [app/Controllers/HomeController.php](app/Controllers/HomeController.php)


```php
// embed code
$code = $request->getQueryParam('code', null);

// сүүлд ашигласан эмбэд код session-оос авна
$lastCode = array_key_exists('lastCode', $_SESSION) ? $_SESSION['lastCode']: null;

// эмбэд код ирсэн бөгөөд сүүлд өгсөн эмбэд код оос ялгаатай
if ($code && $code != $lastCode) {
    // Сүүлд ашигласан код шалгана
    $_SESSION['lastCode'] = $code;

    $clientId = $settings['clientId'];                      // мерчант client_id
    $clientSecret = $settings['clientSecret'];              // мерчант client_secret
    $redirectUri = $fullUrl . $settings['redirectUri'];

    // api client
    $client = new CurlClient();  // curl client
    $api = new MerchantApi($settings['apiBaseUrl'], $client);  

    // хэрэглэгчийн access token авах
    $accessToken = $api->getAccessToken($code, $clientId, $clientSecret, $redirectUri);

    // хэрэглэгчийн access token session-д хадгална
    $_SESSION['accessToken'] = $accessToken['accessToken'];

    // access token ашиглаад хэрэглэгчийн мэдээлэл авах
    $userInfo = $api->userInfo($accessToken['accessToken']);

    // Хэрэглэгчийн мэдээлэл sessiond хадгалах
    $_SESSION['userInfo'] = $userInfo;

    // хэрэглэгчийн бааз руу хадгална
    $user = User::firstOrCreate([
            'user_id' => $userInfo['userId'],
        ],[
            'user_id' => $userInfo['userId'],
            'first_name' => $userInfo['firstName'],
            'last_name' => $userInfo['lastName'],
            'phone_number' => $userInfo['phoneNumber'],
            'email' => $userInfo['email'],
    ]);
}
```


##### 3. Invoice үүсгэх
Захиалга хийсэн үед invoice(нэхэмжлэл) үүсгэнэ.

Жишээ код: [app/Controllers/OrderController.php](app/Controllers/OrderController.php)
```php
// захиалга үүсгэх
$order = Order::create([
    'user_id' => $userInfo['userId'],
    'amount' => $amount,
    'status' => Order::STATUS_PENDING,
]);

// curl client
$client = new CurlClient();
// api client
$api = new MerchantApi($settings['apiBaseUrl'], $client);

// duration
$duration = 60 * 1000;

// description
$description = '#' . $order->id;

// success url
$successUri = $fullUrl . $settings['successUri'] . $order->id;

// tracking data
$trackingData = $order->id;

// invoice(нэхэмжлэл) үүсгэх
$invoice = $api->createInvoice($accessToken, $amount, $duration, $description, $successUri, $trackingData);

// захиалгад нэхэмжлэл дугаар холбох
$order->invoice_number = $invoice['invoiceNumber'];
$order->save();
```



##### 4. Хэрэглэгчид төлбөр төлөх товч харуулах
Yүсгэсэн invoice ашиглаад хэрэглэгчдэд төлбөр төлөх товч харуулах

Жишээ код: [resources/views/order/create.twig](resources/views/order/create.twig)

```html
<script src="https://cdn.lend.mn/3rdparty/embedded/and-ds.js" type="text/javascript"></script>
<div class="col-sm-12 col-md-10 col-md-offset-1">
    <p>
        Захиалга амжилттай бүртгэлээ.<br>
        Таны захиалгын дугаар <b>#{{ order.id }}</b><br>
        Та төлбөрөө төлнө үү <br><br><br>
    </p>
    <div id="and-ds">
        <script type="text/javascript">
            ANDDS.button({
                "container": "and-ds",
                "invoiceNumber": "{{ invoice['invoiceNumber'] }}",
                "amount": "{{ order['amount'] }}",
                "callback": function (params) {
                    alert(JSON.stringify(params));
                }
            });
        </script>
    </div>
</div>
```

##### 5. Хэрэглэгч төлбөр төлсөн тохиолдолд $successUri-г дуудна

Жишээ код: [app/Controllers/OrderController.php](app/Controllers/OrderController.php)
    
```php
// Захиалгын дугаар
$orderId = $request->getQueryParam('order_id', null);
// Захиалга
$order = Order::query()->find($orderId);

// curl client
$client = new CurlClient();
// api client
$api = new MerchantApi($settings['apiBaseUrl'], $client);

// Хэрэглэгчийн токэн
$accessToken = $_SESSION['accessToken'];
// Нэхэмжлэлийн дэлгэрэнгүй
$invoice = $api->invoiceDetail($accessToken, $order->invoice_number);

// Нэхэмжлэлийн длэгэрэнгүй
$status = $invoice->status;
// нэхэмжлэлийн төлөв
switch ($status){
    //0: Хүлээгдэж байгаа
    case 0:
        $order->status = Order::STATUS_PENDING;
        break;
    //1: Төлөгдсөн
    case 1:
        $order->status = Order::STATUS_COMPLETE;
        break;
    //2: Цуцлагдсан
    case 2:
        $order->status = Order::STATUS_CANCELED;
        break;
    //3: Хугацаа нь дууссан.
    case 3:
        $order->status = Order::STATUS_FAILED;
        break;
}
$order->save();
```
    
##### 6. Webhook 
Yүссэн invoice төлөв өөрчлөгдөх үед webhook дуудагдана.

Жишээ код: [app/Controllers/WebhookController.php](app/Controllers/WebhookController.php)

```php
// json data received
$content = trim(file_get_contents("php://input"));
$decoded = json_decode($content, true);
//If json_decode failed, the JSON is invalid.
if (!is_array($decoded)) {
    throw new Exception('Received content contained invalid JSON!');
}

// received event
$eventType = $decoded['eventType'];
$data = $decoded['data'];
$signature = $decoded['signature'];

//
unset($decoded['signature']);

// public key
$publicKey = $settings['dataDir'] . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'public.pem';
$publicKeyPem = openssl_pkey_get_public(file_get_contents($publicKey));
$dataNotVerified = json_encode($decoded, JSON_UNESCAPED_UNICODE);
// verify signature
$isValid = openssl_verify($dataNotVerified, base64_decode($signature), $publicKeyPem, 'sha256WithRSAEncryption');

// if data is not valid
if (0 == $isValid) {
    throw new Exception('Signature error!');
};

// find order by invoice number
$order = Order::query()->where('invoice_number', '=', $data['invoiceNumber'])->first();

//
switch ($eventType) {
    case 'invoice.paid':
        $order->status = Order::STATUS_COMPLETE;
        $order->save();
        break;
    case 'invoice.cancelled':
        $order->status = Order::STATUS_CANCELED;
        $order->save();
        break;
    case 'invoice.expired':
        $order->status = Order::STATUS_FAILED;
        $order->save();
        break;
}
```
