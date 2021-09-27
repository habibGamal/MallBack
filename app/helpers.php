<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('dumph')) {
    function redirectJson($type){
        switch ($type) {
            case 'LOGIN':
                return new JsonResponse(['location' => '/product', 'message' => 'You are alrady logged in!'], 302);
            default:
                return;
        }
    }
}
if (!function_exists('dumph')) {
    function dumph($var){
        header('Access-Control-Allow-Origin: http://mallonlineback.co:3000');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');
        dump($var);
    }
}
