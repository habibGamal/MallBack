<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    // => notify

    public function notifyUserFromBranch(Request $request, $branch_id, $user_id, $msg)
    {
        return Notification::create([
            's_branch_id' => $branch_id,
            'r_user_id' => $user_id,
            'message' => $msg,
        ]);
    }



    public function notifyBranchFromUser(Request $request, $id, $msg)
    {
        $user_id = $request->user('user')->id;
        return Notification::create([
            'r_branch_id' => $id,
            's_user_id' => $user_id,
            'message' => $msg,
        ]);
    }

    public function notifyBranchsFromUser(Request $request, $ids, $msg)
    {
        // we can get it from the caller
        $user_id = $request->user('user')->id;
        $createRequest = [];
        foreach ($ids as $_ => $id) {
            $createRequest[] = [
                's_user_id' => $user_id,
                'r_branch_id' => $id,
                'message' => $msg,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
        return Notification::insert($createRequest);
    }

    // => get notifications

    public function getNotificationsForUser(Request $request)
    {
        $notifications = $request->user('user')->notifications()->select([
            'id', 's_branch_id', 'message', 'seen', 'created_at'
        ])->get()->load('sBranch:id,name,logo')->toArray();
        $blockNotifications = [];
        foreach ($notifications as $_ => $notification) {
            $branch = $notification['s_branch'];
            if (!array_key_exists($branch['id'], $blockNotifications)) {
                $blockNotifications[$branch['id']] = [
                    'id' => $branch['id'],
                    'name' => $branch['name'],
                    'avatar' => $branch['logo'],
                    'notifications' => [],
                ];
            }
            $blockNotifications[$branch['id']]['notifications'][] = [
                'id' => $notification['id'],
                'message' => $notification['message'],
                'seen' => $notification['seen'],
                'created_at' => $notification['created_at'],
            ];
        }
        return array_values($blockNotifications);
    }

    public function getNotificationsForBranches(Request $request)
    {
        $store = $request->user('admin')->store->id;
        if ($store) {
            return Store::with([
                'branches:id,logo,short_name,store_id',
                'branches.notifications:id,message,r_branch_id,created_at,seen'
            ])
                ->select(['id'])
                ->where('id', $store)
                ->get()
                ->flatMap->branches;
        }
        return false;
    }

    // => seen
    public function seenNotificationsForUsers(Request $request)
    {
        $notifications = $request->user('user')->notifications()->select(['id'])->where('seen',0)->get();
        if($notifications){
            return Notification::whereIn('id', $notifications)->update(['seen' => 1]);
        }
        return false;
    }

    public function seenNotificationsForBranches(Request $request)
    {
        $notifications = Notification::select(['id', 'r_branch_id'])->findOrFail($request->ids);
        $branches = $request->user('admin')->store->branches()->select(['id'])->get()->map->id->toArray();
        $seen_notifications = [];
        foreach ($notifications as $_ => $notification) {
            if (in_array($notification->r_branch_id, $branches)) {
                $seen_notifications[] = $notification->id;
            }
        }
        return Notification::whereIn('id', $seen_notifications)->update(['seen' => 1]);
    }
}
