<?php

use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

if (!function_exists('dumph')) {
    // => ''
    function redirectJson($type)
    {
        switch ($type) {
            case 'LOGIN':
                return new JsonResponse(['location' => '/', 'message' => 'userAlreadyAuthenticated'], 302);
            case 'ADMIN_NOT_COMPLETE':
                return new JsonResponse(['location' => '/', 'message' => 'adminNotComplete'], 403);
            case 'ADMIN_NOT_OWN_BRANCH':
                return new JsonResponse(['location' => '/', 'message' => 'adminNotOwnBranch'], 401);
            default:
                return;
        }
    }
}
if (!function_exists('dumph')) {
    function dumph($var)
    {
        header('Access-Control-Allow-Origin: http://localhost:3000');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        dump($var);
    }
}
if (!function_exists('childrenLevel')) {
    function childrenLevel($children, $level)
    {
        $children->map(function ($item) use ($level) {
            $newLevel = $level + 1;
            $item->update([
                'level' => $newLevel,
            ]);
            $items = (new CategoryResource($item))->sub_categories->collect();
            if (count($items->all()) > 0) {
                childrenLevel($items, $newLevel);
            }
        });
    }
}

if (!function_exists('savePhotos')) {
    function savePhotos($photos, $positions)
    {
        $result = [];
        for ($i = 0; $i < count($photos); $i++) {
            $photo = [];
            // => get the position of each picture (decode it to encode [path,position] together)
            $position = $positions[$i] ? json_decode($positions[$i]) : null;
            // => choose disk (locally or on google drive)
            if (env('DISK', 'google') === 'google') {
                // => store it in disk
                $path = $photos[$i]->store('', 'google');
                // => get its url to save it in database
                $url = Storage::disk('google')->url($path);
                // => push the path and the position in picturesStore
                $photo = ['path' => $url, 'position' => $position];
            } else {
                $path = $photos[$i]->storePublicly('public/products');
                $photo = ['path' => $path, 'position' => $position];
            }
            $result[] = $photo;
        }
        return json_encode($result);
    }
}
