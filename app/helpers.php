<?php

use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;

if (!function_exists('dumph')) {
    function redirectJson($type){
        switch ($type) {
            case 'LOGIN':
                return new JsonResponse(['location' => '/', 'message' => 'You are alrady logged in!'], 302);
            default:
                return;
        }
    }
}
if (!function_exists('dumph')) {
    function dumph($var){
        header('Access-Control-Allow-Origin: http://localhost:3000');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        dump($var);
    }
}
if (!function_exists('childrenLevel')) {
    function childrenLevel($children,$level){
        $children->map(function ($item)use($level) {
            $newLevel = $level + 1;
            $item->update([
                'level' => $newLevel,
            ]);
            $items = (new CategoryResource($item))->sub_categories->collect();
            if(count($items->all()) > 0 ){
                childrenLevel($items,$newLevel);
            }
        });
    }
}
