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
        return Product::select('id','pictures','name','price','offer_price')->get();
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
            $position = $request->pictures_position[$key];
            if(env('DISK','google') === 'google'){
                $path = $picture->store('','google');
                $url = Storage::disk('google')->url($path);
                $picturesStore[] = ['path'=>$url,'position'=>$position];
            }else{
                $path = $picture->storePublicly('public/products');
                $picturesStore[] = ['path'=>$path,'position'=>$position];
            }
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
        
        return $product;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        return Product::findOrFail($id);
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
