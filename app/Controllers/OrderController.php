<?php

namespace App\Controllers;


use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;

use App\Classes\LendMNApi\CurlClient;
use App\Classes\LendMNApi\MerchantApi;

class OrderController extends BaseController
{


    /**
     * Захиалга үүсгэх
     * @param $request
     * @param $response
     * @return mixed
     */
    public function create($request, $response)
    {
        $settings = $this->container->get('settings');

        // TODO check user is exist in session

        // Хэрэглэгчийн токэн
        $accessToken = $_SESSION['accessToken'];

        // Хэрэглэгчийн мэдээлэл
        $userInfo = $_SESSION['userInfo'];


        // base url
        $uri = $request->getUri();
        $baseUrl = $uri->getBaseUrl();
        $fullUrl = (string) $baseUrl;

        // todo
        $cart = [];
        if (array_key_exists('cart', $_SESSION)) {
            $cart = $_SESSION['cart'];
        }

        // calculate total amount
        $amount = 0;

        foreach ($cart as $item) {
            $amount += $item['quantity'] * $item['price'];
        }


        // first create order
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

        // нэхэмжлэл үүсгэх
        $invoice = $api->createInvoice($accessToken, $amount, $duration, $description, $successUri, $trackingData);


        // update нэхэмжлэлийн дугаар
        $order->invoice_number = $invoice['invoiceNumber'];
        $order->save();


        foreach ($cart as $product_id => $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product_id,
                'quantity' => $item['quantity'],
            ]);
        }


        // Сагс хоослоно
        $_SESSION['cart'] = [];


        return $this->container->view->render($response, 'order/create.twig', [
            'order' => $order,
            'invoice' => $invoice,
            'categories' => Category::query()->whereNull('parent_id')->get(),
        ]);
    }

    /**
     *
     * @param $request
     * @param $response
     * @return
     */
    public function success($request, $response)
    {
        // Захиалгын дугаар
        $orderId = $request->getQueryParam('order_id', null);

        // Захиалга
        $order = Order::query()->find($orderId);

        // settings
        $settings = $this->container->get('settings');

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

        //
        return $this->container->view->render($response, 'order/success.twig', [
            'order' => $order,
            'invoice' => $invoice,
            'categories' => Category::query()->whereNull('parent_id')->get(),
        ]);
    }


    /**
     * Захиалгын жагсаалт
     * @param $request
     * @param $response
     * @return
     */
    public function list($request, $response){
        // Хэрэглэгчийн мэдээлэл
        $userInfo = $_SESSION['userInfo'];

        // Хэрэглэгчийн дугаар
        $userId = $userInfo['userId'];

        // Захиалга
        $list = Order::query()->where('user_id', '=', $userId)->get();

        //
        return $this->container->view->render($response, 'order/success.twig', [
            'list' => $list,
            'categories' => Category::query()->whereNull('parent_id')->get(),
        ]);

    }
}