<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    private $notifi;
    private $states = [
        'reject' => 'reject',
        'accept' => 'accept',
        'pending' => 'pending',
        'conflict' => 'conflict',
    ];
    public function __construct()
    {
        $this->middleware('auth:admin')->only(['getOrdersForBranch']);
        $this->notifi = new NotificationController();
    }

    public function rejectProductFromOrder(Request $request, $item_id, $order_id, $branch_id)
    {
        // => get item
        $item = $request->user('admin')
            ->store()->select(['id'])->first()
            // => change its state from pending to reject
            ->branches()->select(['id'])->findOrFail($branch_id)
            ->orderedItems()->where('id', $item_id)
            ->update(['state' => $this->states['reject']]);
        // => notify the order owner
        $order = Order::select(['id', 'user_id', 'status'])->findOrFail($order_id);
        $this->notifi->notifyUserFromBranch($request, $branch_id, $order->user_id, 'There is an item in your order has been Rejected');
        // // => Determine order state
        // $this->determineOrderState($order);
        // => return true / false for operation
        return $item;
    }
    public function acceptOrder(Request $request, $branch_id, $order_id)
    {
        // => get item
        $request->user('admin')
            ->store()->select(['id'])->first()
            // => change its state from pending to reject
            ->branches()->select(['id'])->findOrFail($branch_id)
            ->orderedItems()->where('order_id', $order_id)
            ->update(['state' => $this->states['accept']]);
        $order = Order::select(['id', 'user_id', 'status'])->findOrFail($order_id);
        $this->notifi->notifyUserFromBranch($request, $branch_id, $order->user_id, 'Some items in your order have been accepted');
        $this->determineOrderState($order);
        return $this->states['accept'];
    }
    public function rejectOrder(Request $request, $branch_id, $order_id)
    {
        // => get item
        $request->user('admin')
            ->store()->select(['id'])->first()
            // => change its state from pending to reject
            ->branches()->select(['id'])->findOrFail($branch_id)
            ->orderedItems()->where('order_id', $order_id)
            ->update(['state' => $this->states['reject']]);
        $order = Order::select(['id', 'user_id', 'status'])->findOrFail($order_id);
        $this->notifi->notifyUserFromBranch($request, $branch_id, $order->user_id, 'Some items in your order have been rejected');
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
            return $request->user('admin')->store()->select(['id'])->first()
                ->branches()->select(['id'])->first()
                ->orderedItems()->where('state', '!=', $this->states['reject'])->get()->load('product:id,name,offer_price,stock')
                ->mapToGroups(function ($item) {
                    return [$item['order_id'] => $item];
                })
                ->map(function ($items) {
                    return ['id' => $items[0]->order_id, 'ordered_items' => $items];
                })->sortDesc()->values();
        }
        // => required id
        return $request->user('admin')->store()->select(['id'])->first()
            ->branches()->select(['id'])->findOrFail($id)
            ->orderedItems()->where('state', '!=', $this->states['reject'])->get()->load('product:id,name,offer_price,stock')
            ->mapToGroups(function ($item) {
                return [$item['order_id'] => $item];
            })
            ->map(function ($items) {
                return ['id' => $items[0]->order_id, 'ordered_items' => $items];
            })->sortDesc()->values();
    }



    private function determineOrderState($order)
    {
        $items = $order->orderedItems()->select(['id', 'state'])->get()->map->state;
        $states = [
            'pending' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'delivering' => 0,
        ];
        foreach ($items as $item) {
            if ($item == $this->states['pending'])
                $states['pending']++;
            if ($item == $this->states['accept'])
                $states['accepted']++;
            if ($item == $this->states['reject'])
                $states['rejected']++;
            // if ($item == $this->states['delivering'])
            //     $states['delivering']++;
        }
        function getFinalState($states)
        {
            if ($states['pending'] != 0)
                return 'pending';
            if ($states['rejected'] != 0 && $states['accepted'] != 0)
                return 'conflict';
            if ($states['rejected'] != 0)
                return 'rejected';
            if ($states['accepted'] != 0)
                return 'accepted';
            if ($states['delivering'] != 0)
                return 'delivering';
            return 'pending';
        }
        return $order->update(['status' => getFinalState($states)]);
    }
}
