<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderedItem;
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

    private function determineOrderState($order)
    {
        $items = $order->orderedItems()->select(['id', 'state'])->get()->map->state;
        $states = [
            'pending' => 0,
            'accepted' => 0,
            'delivering' => 0,
        ];
        foreach ($items as $item) {
            if ($item == 'pending')
                $states['pending']++;
            if ($item == 'accepted')
                $states['accepted']++;
            if ($item == 'delivering')
                $states['delivering']++;
        }
        function getFinalState($states)
        {
            if ($states['pending'] != 0)
                return 'pending';
            if ($states['accepted'] != 0)
                return 'accepted';
            if ($states['delivering'] != 0)
                return 'delivering';
        }
        return $order->update(['status' => getFinalState($states)]);
    }

    public function acceptOrder(Request $request, $branch_id, $order_id)
    {
        $branch = $request->user('admin')->store->branches()->select(['id'])->findOrFail($branch_id);
        $branch->orderedItems()->where('order_id', $order_id)->update(['state' => 'accepted']);
        $order = Order::select(['id', 'status'])->findOrFail($order_id);
        $this->notifi->notifyUserFromBranch($request, $branch_id, $order->user->id, 'Your order has been accepted');
        return $this->determineOrderState($order);
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
            return $request->user('admin')->store->branches[0]->orders()->select(['orders.id'])->get()->load(['orderedItems', 'orderedItems.product:id,name,offer_price,stock']);
        }
        // => required id
        return $request->user('admin')->store->branches()->findOrFail($id)->orders()->select(['orders.id'])->get()->load(['orderedItems', 'orderedItems.product:id,name,offer_price,stock']);
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
        // => get the order
        $order = $request->user('user')->orders()->findOrFail($order_id);
        // => get branches ids to send notification to them
        $branches_id = $order->branches()->select(['branches.id'])->get()->map->id;
        $this->notifi->notifyBranchsFromUser($request, $branches_id, 'The customer cancel his order');
        // => delete order items
        $order->orderedItems()->delete();
        // => delete the order
        return  $order->delete();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeProductFromOrder(Request $request, $item_id, $order_id)
    {
        // => get the order
        $order = $request->user('user')->orders()->select(['id', 'total_cost', 'shipping_cost'])->findOrFail($order_id);
        // => get the item will be removed with product offer_price
        $removedItem = $order->orderedItems()->findOrFail($item_id)->load('product:id,offer_price,name');
        // => delete the item
        $removedItem->delete();
        // => update the total_cost
        $newTotalPrice =  $order->total_cost - ($removedItem->product->offer_price * $removedItem->count);
        // => if the new total_price == shipping_cost this means that no items in the order ,so delete it
        if ($newTotalPrice == $order->shipping_cost) {
            return $order->delete();
        }
        $order->total_cost = $newTotalPrice;
        $this->notifi->notifyBranchFromUser($request, $removedItem->branch_id, "Item ({$removedItem->product->name}) has been removed from order $order->id review the order please");
        return $order->save();
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
        return $user->orders->load(['orderedItems.branch:id,name', 'orderedItems.product:id,pictures,name,offer_price']);
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
        $products_price_sum = $user->cart
            ->products()
            ->select(['id', 'offer_price'])
            ->get()
            ->sum(
                function ($product) {
                    return $product->offer_price * $product->pivot->product_count;
                }
            );
        $shippingCost = $this->calcShippingCost();
        $total_sum = $products_price_sum + $shippingCost;
        // get [product_id,product_count] pivots from current cart and branch_id for each product_id
        $cartId = $user->cart->id;
        $product_count_branch_pivots = DB::table('cart_product')
            ->join('branch_product', 'cart_product.product_id', '=', 'branch_product.product_id')
            ->select('cart_product.product_id', 'cart_product.product_count', 'branch_product.branch_id')
            ->where('cart_id', $cartId)
            ->get();
        $order = Order::create([
            'user_id' => $user->id,
            'shipping_cost' => $shippingCost,
            'total_cost' => $total_sum,
        ]);
        $items = []; // which will be stored in order_product pivots
        $branches_id = []; // to notify them
        foreach ($product_count_branch_pivots as $_ => $pivot) {
            $items[] = ['product_id' => $pivot->product_id, 'count' => $pivot->product_count, 'branch_id' => $pivot->branch_id, 'state' => 'pending', 'order_id' => $order->id];
            $branches_id[] = $pivot->branch_id;
        }
        $orderedItems = OrderedItem::insert($items);
        // $cartController = new CartItemsController($request);
        // // $cartController->emptyCart();
        $this->notifi->notifyBranchsFromUser($request, $branches_id, 'You have new Order');
        return $products_price_sum;
    }
}
