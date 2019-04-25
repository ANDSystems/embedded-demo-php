<?php

namespace App\Controllers;


use App\Models\Order;

class WebhookController extends BaseController
{
    public function index($request, $response)
    {
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

        // find order by invoice number
        $order = Order::query()->where('invoice_number', '', $data['invoiceNumber'])->first();


        switch ($eventType) {
            case 'invoice.paid':
                //
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

    }
}