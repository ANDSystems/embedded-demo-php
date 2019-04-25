<?php

namespace App\Controllers;


use App\Models\Product;
use App\Models\Category;

class CartController extends BaseController
{


    public function list($request, $response){
        // todo
        $cart = [];
        if (array_key_exists('cart', $_SESSION)){
            $cart = $_SESSION['cart'];
        }

        return $this->container->view->render($response, 'cart/header.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * Сагсанд бараа нэмэх
     * @param $request
     * @param $response
     * @return mixed
     */
    public function add($request, $response)
    {
        $id = $request->getQueryParam('id');
        $quantity = $request->getQueryParam('quantity', 1);

        // барааны жагсаалт
        $product = Product::query()->find($id);

        // todo
        $cart = [];
        if (array_key_exists('cart', $_SESSION)){
            $cart = $_SESSION['cart'];
        }

        if (array_key_exists($id, $cart)){
            $quantity += $cart[$id]['quantity'];
        }
        $cart[$id] = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $product->image,
            'quantity' => $quantity,
            'totalprice' => $quantity * $product->price,
        ];

        //
        $_SESSION['cart'] = $cart;


        return $this->container->view->render($response, 'cart/header.twig', [
            'cart' => $cart,
        ]);

    }

    /**
     * Сагснаас бараа хасах
     * @param $request
     * @param $response
     * @return mixed
     */
    public function remove($request, $response){
        $id = $request->getQueryParam('id');

        // todo
        $cart = [];
        if (array_key_exists('cart', $_SESSION)){
            $cart = $_SESSION['cart'];
        }

        // remove item from cart
        if (array_key_exists($id, $cart)){
            unset($cart[$id]);
        }

        //
        $_SESSION['cart'] = $cart;


        //
        return $this->container->view->render($response, 'cart/checkout.twig', [
            'cart' => $cart,
            'categories' => Category::query()->whereNull('parent_id')->get(),
        ]);
    }


    /**
     *
     * @param $request
     * @param $response
     * @return mixed
     */
    public function checkout($request, $response){
        $cart = [];
        if (array_key_exists('cart', $_SESSION)){
            $cart = $_SESSION['cart'];
        }

        return $this->container->view->render($response, 'cart/checkout.twig', [
            'cart' => $cart,
            'categories' => Category::query()->whereNull('parent_id')->get(),
        ]);
    }

}