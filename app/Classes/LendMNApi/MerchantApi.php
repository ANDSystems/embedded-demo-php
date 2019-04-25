<?php

namespace App\Classes\LendMNApi;

class MerchantApi
{

    protected $client;
    protected $baseUri;

    public function __construct($baseUri, HttpClientInterface $client)
    {
        $this->baseUri = $baseUri;
        $this->client = $client;
    }

    /**
     * Мерчант хэрэглэгчийн access_token авах
     * @param $code
     * @param $clientId
     * @param $clientSecret
     * @param $redirectUri
     * @return mixed
     */
    public function getAccessToken($code, $clientId, $clientSecret, $redirectUri)
    {
        $result = $this->client->post($this->baseUri . '/api/oauth/v2/token', [
            'data' => [
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code'
            ]
        ]);

        return $result['response'];
    }


    /**
     * Хэрэглэгчийн дэлгэрэнгүй мэдээллийг авах
     * @param $accessToken
     * @return mixed
     */
    public function userInfo($accessToken)
    {
        $result = $this->client->get($this->baseUri . '/api/user/info', [
            'header' => [
                'x-and-auth-token: ' . $accessToken
            ]
        ]);

        return $result['response'];
    }


    /**
     * Нэхэмжлэл үүсгэх. Мерчантаас хэрэглэгчид нэхэмжлэл үүсгэх.
     * @param $accessToken
     * @param $amount
     * @param $duration
     * @param $description
     * @param $successUri
     * @param $trackingData
     * @return mixed
     */
    public function createInvoice($accessToken, $amount, $duration, $description, $successUri, $trackingData)
    {
        $result = $this->client->post($this->baseUri . '/api/w/invoices', [
            'header' => [
                'x-and-auth-token: ' . $accessToken
            ],
            'data' => [
                'amount' => $amount,
                'duration' => $duration,
                'description' => $description,
                'successUri' => $successUri,
                'trackingData' => $trackingData,
            ]
        ]);

        return $result['response'];
    }


    /**
     * Үүсгэсэн нэхэмжлэлийг устгах. Өөрийн үүсгэсэн нэхэмжлэлийг цуцлах.
     * @param $accessToken
     * @param $invoiceNumber
     * @return mixed
     */
    public function cancelInvoice($accessToken, $invoiceNumber)
    {
        $result = $this->client->delete($this->baseUri . '/api/invoices/' . $invoiceNumber, [
            'header' => [
                'x-and-auth-token: ' . $accessToken
            ],
        ]);

        return $result['response'];
    }


    /**
     * Нэхэмжлэл төлөгдсөн эсэхийг шалгах
     * @param $accessToken
     * @param $invoiceNumber
     * @return mixed
     */
    public function invoiceDetail($accessToken, $invoiceNumber)
    {
        $result = $this->client->get($this->baseUri . '/api/w/invoices/' . $invoiceNumber, [
            'header' => [
                'x-and-auth-token: ' . $accessToken
            ],
        ]);

        return $result['response'];

    }


}