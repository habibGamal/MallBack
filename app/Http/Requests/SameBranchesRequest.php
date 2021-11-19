<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SameBranchesRequest extends FormRequest
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


    public function attributes()
    {
        return [
            'short_branch_names.*' => 'shortcut name',
            'addresses.*' => 'address'
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $branches_number = $request->branches_number;
        return [
            'store_name' => 'required|string|max:255',
            'short_branch_names' => "required|array|size:{$branches_number}",
            'short_branch_names.*' => 'required|string',
            'addresses' => "required|array|size:{$branches_number}",
            'addresses.*' => 'required|string',
            'logo' => 'required|mimes:png,jpg,jpeg,webp|max:1024',
            'logo_position' => 'string|nullable',
        ];
    }
}
