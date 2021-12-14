<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth:admin' ,'adminComplete'])->except('index','show');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::select('id', 'pictures', 'name', 'price', 'offer_price')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest  $request)
    {
        // => validate request
        $request->validated();
        // => handle pictures
        $jsonPictures = savePhotos($request->file('pictures'),$request->pictures_position);
        // => create product
        $product = Product::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'offer_price' => $request->input('offer_price'),
            'category_id' => $request->input('category'),
            'stock' => $request->input('stock'),
            'returnable' => $request->input('returnable'),
            'description' => $request->input('description'),
            'specifications' => $request->input('specifications'),
            'brand' => $request->input('brand'),
            'warranty' => $request->input('warranty'),
            'pictures' => $jsonPictures,
        ]);
        // => create colors option if it exists
        if ($colors = $request->input('colors_option')) {
            Option::create([
                'name' => 'colors_option',
                'body' => $colors,
                'product_id' => $product->id,
            ]);
        }
        // => create sizes option if it exists
        if ($sizes = $request->input('sizes_option')) {
            Option::create([
                'name' => 'sizes_option',
                'body' => $sizes,
                'product_id' => $product->id,
            ]);
        }

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

        return Product::with([
                'category:id,name,parent_id',
                'category.parent_category:id,name,parent_id',
                'category.parent_category.parent_category:id,name',
                'options'
            ])
            ->findOrFail($id);
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
        return Product::destroy($id);
    }

    /**
     * Remove the list of resources from storage.
     *
     * @param  array  $ids
     * @return \Illuminate\Http\Response
     */
    public function destroyList(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);
        // dumph($request->ids);
        return Product::destroy($request->ids);
    }
}
