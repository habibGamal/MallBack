<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $notifi;
    public function __construct()
    {
        $this->middleware('auth:user')->only(['store', 'getOrdersForUser', 'removeProductFromOrder', 'cancelOrder']);
        $this->middleware('auth:admin')->only(['getOrdersForBranch']);
        $this->notifi = new NotificationController();
    }


    public function acceptOrder(Request $request,$branch_id,$order_id){
        $branch = $request->user('admin')->store->branches()->select(['id'])->findOrFail($branch_id);
        $order = $branch->orders()->select(['id','user_id'])->findOrFail($order_id);
        $order->status = 'accepted';
        $order->save();
        $this->notifi->notifyUserFromBranch($request,$branch_id,$order->user->id,'Your order has been accepted');
        return true;
    }

    /**
     * Display orders for a branch
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOrdersForBranch(Request $request, $id)
    {
        // => default id
        if (!$id) {
            $defaultId = $request->user('admin')->store->branches[0]->id;
            return Branch::select(['id'])->where('id', $defaultId)->with([
                'orders'=> function ($query){
                    $query->latest();
                },
                'orders.products' => function ($query) use ($id) {
                    $query->where('branch_id', $id);
                }
            ])->get()[0]->orders;
        }
        // => required id
        return Branch::select(['id'])->where('id', $id)->with([
            'orders'=> function ($query){
                $query->latest();
            },
            'orders.products' => function ($query) use ($id) {
            $query->where('branch_id', $id);
        }])->get()[0]->orders;
    }
    public function calcShippingCost()
    {
        return 10;
    }

    // => user site
    /**
     * Cancel order
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder(Request $request, $order_id)
    {
        $order = $request->user('user')->orders->find($order_id);
        if ($order) {
            $branches_id = $order->branches()->select(['id'])->get()->map->id;
            $this->notifi->notifyBranchsFromUser($request, $branches_id, 'The customer cancel his order');
            $order->products()->detach();
            return  $order->delete();
        }
        return false;
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeProductFromOrder(Request $request, $product_id, $order_id)
    {
        $order = $request->user('user')->orders->find($order_id);
        if ($order) {
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
        return $request->user('user')->with(['orders'=> function($query){$query->latest();}, 'orders.products:id,pictures,name,price', 'orders.products.branches:id,name'])->where('id', $user->id)->get(['id'])[0]->orders;
    }

    /**
     * user make order
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
        $this->notifi->notifyBranchsFromUser($request, $order_branch_pivots, 'You have new Order');
        return $order;
    }
}
