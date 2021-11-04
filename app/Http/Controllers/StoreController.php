<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\RegisterAdminController;
use App\Http\Requests\BranchRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    private function storeRules(){
        return [
            'governorate' => 'required|string',
            'can_return' => 'required|boolean',
            'business_type' => 'required|string',
            'work_from' => 'required|boolean',
            'holidays' => 'required|array',
            'holidays.*' => [
                Rule::in([
                    'Friday',
                    'Saturday',
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                ])
            ],
            'work_hours' => 'required|array',
            'work_hours.from' => 'required|integer|min:1|max:12',
            'work_hours.to' => 'required|integer|min:1|max:12',
            'work_hours.p1' => ['required', Rule::in(['am', 'pm',])],
            'work_hours.p2' => ['required', Rule::in(['am', 'pm',])],
    
        ];
    }
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
    public function store(Request $request,BranchController $branchController,RegisterAdminController $registerAdminController)
    {
        // dumph($request->all());
        // => validation {{ I am not using StoreRequest to validate as I validate more than one thing (branch,admin) }}
        $request->validate($this->storeRules());
        // => create admin account
        $admin = $registerAdminController->register($request);
        // => create store
        $store = Store::create([
            'governorate' => $request->input('governorate'),
            'can_return' => $request->input('can_return'),
            'business_type' => $request->input('business_type'),
            'work_from' => $request->input('work_from'),
            'holidays' => implode(',', $request->input('holidays')),
            'work_hours' => implode(',', $request->input('work_hours')),
            'admin_id' => $admin->id,
        ]);
        $branch = $branchController->store($request, 4);
        return $branch;
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
