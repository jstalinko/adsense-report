<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['users'] = User::all();
        return Inertia::render('users/Index', $data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->is_admin,
            'email_verified_at' => now()
        ]);
        if ($user) {
            return redirect()->to('/users')->with('success', 'Success add users');
        } else {
            return redirect()->to('/users')->with('error', 'Error to add users');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $user = User::find($id);
      
      $updated = $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password == '' ? $user->password : Hash::make($request->password),
            'is_admin' => $request->is_admin,
            'email_verified_at' => now()
        ]);
        if ($updated) {
            return redirect()->to('/users')->with('success', 'Success update users');
        } else {
            return redirect()->to('/users')->with('error', 'Error to update users');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if ($user->delete()) {
            return redirect()->to('/users')->with('success', 'Success delete users');
        } else {
            return redirect()->to('/users')->with('error', 'Failed delete users');
        }
    }
}
