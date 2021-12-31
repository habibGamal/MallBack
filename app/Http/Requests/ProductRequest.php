<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required|min:3|max:255|string',
            'price'=>'required|numeric|min:0',
            'offer_price'=>'nullable|numeric',
            'category'=>'required|integer|min:0',
            'stock'=>['required',Rule::in(['0','1','2'])],
            'branch_id'=>'required|exists:branches,id',
            'returnable'=>'required|boolean',
            'description'=>'nullable|string',
            'specifications'=>'nullable|string',
            'brand'=>'nullable|string',
            'warranty'=>'nullable|string',
            'pictures'=>'required',
            'pictures.*'=>'mimes:png,jpg,jpeg,webp|max:1024',
            'colors_option'=>'nullable|json',
            'sizes_option'=>'nullable|json',
        ];
    }
}
