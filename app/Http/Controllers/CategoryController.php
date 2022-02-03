<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = CategoryResource::collection(Category::whereNull('parent_id')->get());
        return $categories;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request  $request)
    {
        $parent_id = $request->input('parent_id') == 0 ? null : $request->input('parent_id');
        $level = $parent_id !== null ? Category::find($parent_id)->level + 1 : 0;
        $request->validate([
            'name' => 'required',
            'parent_id' => 'numeric'
        ]);
        $category = Category::create([
            'name' => $request->input('name'),
            'parent_id' => $parent_id,
            'level' => $level,
        ]);
        return ;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Category::with(['products:id,category_id,name,price,offer_price,pictures','products.branches:id,name,logo'])->findOrFail($id);
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
        $parent_id = $request->input('parent_id') == 0 ? null : $request->input('parent_id');
        $level = $parent_id !== null ? Category::find($parent_id)->level + 1 : 0;
        $request->validate([
            'name' => 'required',
            'parent_id' => 'numeric'
        ]);
        $category = Category::find($id);
        $sub_categories = (new CategoryResource($category))->sub_categories;
        childrenLevel($sub_categories,$level);
        $category->update([
            'name' => $request->input('name'),
            'parent_id' => $parent_id,
            'level' => $level,
        ]);
        return ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Category::destroy($id);
    }
}
