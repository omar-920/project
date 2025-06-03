<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin') {
            $users = User::paginate(10);
            return response()->json(['users' => $users],201);
        }else{
            return response()->json(['error'=>'Unauthorized You must be Admin'],401);
        }
    }

    public function store(UserRequest $request)
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin') {

            $validated = $request->validated();
            $user = User::create($validated);
            return response()->json(['message' => 'User Created!'],201);

        }else{
            return response()->json(['error'=>'Unauthorized You must be Admin'],401);
        }
    }

    public function update($id,UserRequest $request)
    {
        $user = User::findorfail($id);
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'min:10|max:19',
            'address' => 'string|max:255',
            'role' => 'in:admin,user',
        ]);
        $user->update($validated);
        return response()->json(['message' => $user],201);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'User Deleted!'],201);
    }
}
