<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user')->only(['store','getOrdersForUser','removeProductFromOrder']);
        $this->middleware('auth:admin')->only(['getOrdersForBranch']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder(Request $request,$order_id)
    {
        $order = $request->user('user')->orders->find($order_id);
        $order->products()->detach();
        return  $order->delete();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeProductFromOrder(Request $request,$product_id,$order_id)
    {
        $order = $request->user('user')->orders->find($order_id);
        if($order){
            $order->products()->detach($product_id);
            $order->total_cost = $order->products()->sum('offer_price');
            $order->save();
            return $order;
        }
        return 'false'; // temporary
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOrdersForUser(Request $request)
    {
        $user = $request->user('user');
        return $request->user('user')->with(['orders','orders.products:id,pictures,name,price','orders.products.branches:id,name'])->where('id',$user->id)->get(['id'])[0]->orders;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOrdersForBranch(Request $request, $id)
    {
        $defaultId = $request->user('admin')->store->branches[0]->id;
        if (!$id) {
            return Branch::select(['id'])->where('id', $defaultId)->with(['orders', 'orders.products' => function ($query) use ($id) {
                $query->where('branch_id', $id);
            }])->get()[0]->orders;
        }
        return Branch::select(['id'])->where('id', $id)->with(['orders', 'orders.products' => function ($query) use ($id) {
            $query->where('branch_id', $id);
        }])->get()[0]->orders;
    }
    public function calcShippingCost()
    {
        return 10;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // get the current user id
        $user = $request->user('user');
        // calculate total_cost
        $products_price_sum = $user->cart->products->sum('offer_price');
        $shippingCost = $this->calcShippingCost();
        $total_sum = $products_price_sum + $shippingCost;
        // get [product_id,product_count] pivots from current cart and branch_id for each product_id
        $cartId = $user->cart->id;
        $product_count_branch_pivots = DB::table('cart_product')
            ->join('branch_product', 'cart_product.product_id', '=', 'branch_product.product_id')
            ->select('cart_product.product_id', 'cart_product.product_count', 'branch_product.branch_id')
            ->where('cart_id', $cartId)
            ->get();
        $order_product_pivots = []; // which will be stored in order_product pivots
        $order_branch_pivots = []; // which will be stored in order_product pivots
        foreach ($product_count_branch_pivots as $_ => $pivot) {
            $order_product_pivots[$pivot->product_id] = ['product_count' => $pivot->product_count, 'branch_id' => $pivot->branch_id];
            $order_branch_pivots[] = $pivot->branch_id;
        }
        $order = Order::create([
            'user_id' => $user->id,
            'shipping_cost' => $shippingCost,
            'total_cost' => $total_sum,
        ]);
        $order->products()->attach($order_product_pivots);
        $order->branches()->attach(array_unique($order_branch_pivots));
        $cartController = new CartItemsController($request);
        // $cartController->emptyCart();
        return $order;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
