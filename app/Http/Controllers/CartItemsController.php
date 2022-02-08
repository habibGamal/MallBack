<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartItemsController extends Controller
{
    private $cart;
    public function __construct(Request $request)
    {
        // => to deal with cart user must be authenticated
        $this->middleware('auth:user');
        // => get the cart of the user
        if ($request->user('user')) {
            $this->cart = $request->user('user')->cart;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user('user')->cart->products;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // => add items to the cart
        $request->validate([
            'product_id' => 'required|integer',
            'product_count' => 'nullable|integer',
        ]);

        $product_id = $request->product_id;
        $cart_item = $this->cart->products->firstWhere('id', $product_id);
        if ($cart_item) {
            return message('ITEM_ALREADY_IN_CART',403,'warning');
        }
        $this->cart->products()->attach($product_id);
        return $this->cart->products()->find($product_id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $product_id)
    {
        if($request->product_count !== null){
            $cart_item = $this->cart->products->find($product_id)->pivot;
            if ($cart_item !== null) {
                $cart_item->product_count = $request->product_count;
                $cart_item->save();
            }
            return $cart_item;
        }
        return ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->cart->products()->detach($id);;
    }

    public function emptyCart()
    {
        return $this->cart->products()->detach();;
    }
}
