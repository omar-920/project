<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\WelcomeUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|min:5|max:50',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|regex:/^09\d{8}$/',
                'address' => 'required|string|max:255',
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'],
            ]);

            $user->notify(new WelcomeUser());
            return $this->createdResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 'User registered successfully!');

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (Exception $e) {
            return $this->serverErrorResponse('An unexpected error occurred. Please try again later.');
        }
    }

    public function logout(Request $request)
    {
        try {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $user->currentAccessToken()->delete();
                return $this->apiResponse('success', 'Logged out successfully'); 

        } catch (Exception $e) {
            return $this->serverErrorResponse('An unexpected error occurred during logout.');
        }
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validatedData['email'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                return $this->unauthorizedResponse('Invalid credentials'); // Use trait method
            }

            // It's good practice to check if the email is verified before login,
            // or handle it based on your application's requirements.
            // For now, just sending the notification as in the original code.
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                 $user->sendEmailVerificationNotification();
            }


            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->apiResponse('success', 'Login successful', [ // Use trait method
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors()); // Use trait method
        } catch (Exception $e) {
            // Log::error('Login error: ' . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred during login.'); // Use trait method
        }
    }
}