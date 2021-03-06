<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller {

    /**
     * Create a new home controller instance.
     *
     * @return void
     */
    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $model = User::findOrFail($id);

        return view('settings.edit', compact('model'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param Request $request
     * @return Response
     */
    public function update(Request $request, $id) {
        $model = User::findOrFail($id);
        $request->flash(); //save the input before redirect
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,' . $id
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        } else {
            $model->name = $request->input("name");
            $model->email = $request->input("email");
            $model->target_email = $request->input("target_email");
            $model->target_phone = $request->input("target_phone");

            $model->save();

            return redirect()->route('settings.edit', $id)->with('message', 'Item updated successfully.');
        }
    }

}
