<?php

namespace App\Http\Requests;

use App\Rules\Logos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class DifferentBranchesRequest extends FormRequest
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
            'addresses.*' => 'address',
            'branch_names.*' => 'branch name'
        ];
    }
    public function messages()
    {
        return [
            'logos.required' => 'You must enter the logo fields',
            'logos.size' => 'You must enter :size logos'
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
            'branch_names' => "required|array|size:{$branches_number}",
            'branch_names.*' => 'required|string',
            'addresses' => "required|array|size:{$branches_number}",
            'addresses.*' => 'required|string',
            'logos' => ['required', 'array', "size:{$branches_number}", new Logos($branches_number),],
            'logos.*' => ['required', 'mimes:png,jpg,jpeg,webp', 'max:1024',],
            'logos_position' => "required|array|size:{$branches_number}",
            'logos_position.*' => 'nullable|string',
        ];
    }
}
