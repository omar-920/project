<?php

namespace App\Http\Controllers\auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class ManageUserController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {
        try {
            $users = User::paginate(10);
            return $this->retrievedResponse($users);
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while fetching users.');
        }
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        try {
            $user = User::create($validated);
            return $this->createdResponse($user, 'User Created!');
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while creating the user.');
        }
    }

    public function update($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'sometimes|nullable|min:10|max:19',
                'address' => 'sometimes|nullable|string|max:255',
                'role' => 'sometimes|in:admin,user',
            ]);
            $user->update($validated);
            return $this->updatedResponse($user);
        }catch (ValidationException $e) {
        
            Log::warning('Validation failed for user update (ID: ' . $id . '): ' . json_encode($e->errors()));
            return $this->validationErrorResponse(json_encode($e->errors()));
        }
             catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found.');
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while updating the user.');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === 1) {
                return $this->apiResponse('error', 'This is Super Admin !!!', null, 403);
            }

            $user->tokens()->delete();
            $user->delete();
            return $this->deletedResponse('User Deleted!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found.');
        } catch (Exception $e) {
            Log::error($e);
            return $this->serverErrorResponse('An error occurred while deleting the user.');
        }
    }
}