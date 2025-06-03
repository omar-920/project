<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin') {

            $validated = $request->validate([
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,'.$user->id,
                'phone' => 'min:10|max:19',
                'address' => 'string|max:255',
                'role' => 'in:admin,user',
            ]);
            $user->update($validated);
            return response()->json(['message' => $user],201);
        }else{
            return response()->json(['error'=>'Unauthorized You must be Admin'],401);
        }
    }

    public function destroy($id)
    {

        $user = User::findorfail($id);

        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin') {
            if ($user->id != 1 )
            {
                $user->tokens()->delete();
                $user->delete();
                return response()->json(['message' => 'User Deleted!'],201);
            }else{
                return response()->json(['message' => 'This is Super Admin !!! '],201);
            }
        }else{
            return response()->json(['error'=>'Unauthorized You must be Admin'],401);
        }



    }

    public function profileIndex()
    {
        return response()->json(['user' => \Illuminate\Support\Facades\Auth::user()],201);
    }

    public function profileUpdate(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'min:10|max:19',
            'address' => 'string|max:255',
        ]);
        $user->update($validated);
        return response()->json([
            'message' => 'User Updated!',
            'user' => $user,
            ],201);
    }

    public function profileChangePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:3|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function profileDelete(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

            $user->tokens()->delete();

            $user->delete();
            return response()->json(['message' => 'User Deleted!'],201);

    }
}
