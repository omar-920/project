<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            return $this->retrievedResponse(Auth::user());
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while fetching user data.');
        }
    }
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
                'phone' => 'nullable|min:10|regex:/^09\d{8}$/',
                'address' => 'nullable|string|max:255',
                'current_password' => 'nullable|string|required_with:new_password',
                'new_password' => 'nullable|string|min:3|confirmed',
            ]);

            $user->fill(array_filter([
                'name' => $validatedData['name'] ?? null,
                'email' => $validatedData['email'] ?? null,
                'phone' => $validatedData['phone'] ?? null,
                'address' => $validatedData['address'] ?? null,
            ]));

            if ($request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->validationErrorResponse(['current_password' => 'Current password is not correct.']);
                }
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            return $this->updatedResponse($user->fresh());

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while updating the profile.');
        }
    }


    public function destroy()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->tokens()->delete();

            $user->delete();
            return $this->deletedResponse();

        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while deleting the user.');
        }
    }
}