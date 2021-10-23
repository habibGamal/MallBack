<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\RegisterAdminController;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends RegisterAdminController
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
    public function store(Request $request)
    {
        // dumph($request->all());
        $adminData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'phone_number' => $request->input('phone_number'),
            'card_id' => $request->input('card_id'),
        ];
        // => create admin account
        $admin = $this->create($adminData);
        // => validate the store info
        $request->validate([
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
            'work_hours.p1' => ['required',Rule::in(['am', 'pm',])],
            'work_hours.p2' => ['required',Rule::in(['am', 'pm',])],

        ]);
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
        return $store;
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
