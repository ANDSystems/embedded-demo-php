<?php

namespace App\Controllers;


use App\Models\Order;

class WebhookController extends BaseController
{
    public function index($request, $response)
    {
        // settings
        $settings = $this->container->get('settings');

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

        return $response->withJson([
            'code' => 0, 'response' => ['message' => 'OK']],
            200,
            JSON_UNESCAPED_UNICODE
        );

    }
}