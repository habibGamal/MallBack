<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest  $request)
    {
        // handle picture
        $validation = $request->validated();
        $pictures = $request->file('pictures');
        $picturesStore = [];
        foreach($pictures as $key=>$picture){
            $path = $picture->storePublicly('public/products');
            $position = $request->pictures_position[$key];
            $picturesStore[] = ['path'=>$path,'position'=>$position];
        }
        $product = Product::create([
            'name'=>$request->input('name'),
            'price'=>$request->input('price'),
            'offer_price'=>$request->input('offer_price'),
            'stock'=>$request->input('stock'),
            'returnable'=>$request->input('returnable'),
            'description'=>$request->input('description'),
            'specifications'=>$request->input('specifications'),
            'brand'=>$request->input('brand'),
            'warranty'=>$request->input('warranty'),
            'pictures'=>json_encode($picturesStore),
        ]);
        
        return dumph($product);
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
