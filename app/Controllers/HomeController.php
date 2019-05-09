<?php

namespace App\Controllers;


use App\Models\Product;
use App\Models\Category;
use App\Models\User;


class HomeController extends BaseController
{

    public function index($request, $response)
    {
        $settings = $this->container->get('settings');

        // base url
        $uri = $request->getUri();
        $baseUrl = $uri->getBaseUrl();
        $fullUrl = (string) $baseUrl;

        // embed code
        $code = $request->getQueryParam('code', null);

        // сүүлд ашигласан эмбэд код session-оос авна
        $lastCode = array_key_exists('lastCode', $_SESSION) ? $_SESSION['lastCode']: null;

        // эмбэд код ирсэн бөгөөд сүүлд өгсөн эмбэд код оос ялгаатай
        if ($code && $code != $lastCode) {
            $clientId = $settings['clientId'];
            $clientSecret = $settings['clientSecret'];
            $redirectUri = $fullUrl . $settings['redirectUri'];

            // хэрэглэгчийн access token авах
            $accessToken = $this->container->api->getAccessToken($code, $clientId, $clientSecret, $redirectUri);

            // нэвтрэх access token session-д хадгална
            $_SESSION['accessToken'] = $accessToken['accessToken'];

            // хэрэглэгчийн мэдээлэл авах
            $userInfo = $this->container->api->userInfo($accessToken['accessToken']);

            // Хэрэглэгчийн мэдээлэл сешионд хадгалах
            $_SESSION['userInfo'] = $userInfo;

            // Сүүлд ашигласан код шалгана
            $_SESSION['lastCode'] = $code;

            // хэрэглэгч бааз руу хадгална
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


        // Бүтээгдхүүн шүүлтүүр
        $category_id = $request->getQueryParam('category_id', null);
        $subcategory_id = $request->getQueryParam('subcategory_id', null);

        // бүх ангилал
        $categories = Category::query()->whereNull('parent_id')->get();

        // барааны жагсаалт
        $query = Product::query();
        if ($category_id) {
            $query->where('category_id', '=', $category_id);
        }
        if ($subcategory_id) {
            $query->where('subcategory_id', '=', $subcategory_id);
        }
        $products = $query->get();

        return $this->container->view->render($response, 'home.twig', [
            'categories' => $categories,
            'products' => $products
        ]);
    }
}