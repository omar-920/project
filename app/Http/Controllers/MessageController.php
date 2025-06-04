<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewMessage;
use App\Traits\ApiResponseTrait;

class MessageController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {   
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $messages = $user->messages()
                            ->select('id', 'subject', 'created_at')
                            ->latest()
                            ->paginate(10);
            return $this->retrievedResponse($messages);
        } catch (\Exception $e) {
            Log::error('Error retrieving messages: ' . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while retrieving messages.', 500);
        }
    }

    public function show($id)
    {
        try {
            $message = Message::find($id);
            if (!$message) {
                return $this->notFoundResponse();
            }
            if (Auth::id() !== $message->user_id) {
                return $this->forbiddenResponse();
            }

            return $this->retrievedResponse($message);
        } catch (\Exception $e) {
            Log::error("Error retrieving message with ID {$id}: " . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while retrieving the message.', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|in:suggestion,problem',
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $message = $user->messages()->create($validated);

            $user->notify(new NewMessage($message));

            return $this->createdResponse($message, 'Message sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating message: ' . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while sending the message.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $message = Message::find($id);
            if (!$message) {
                return $this->notFoundResponse();
            }

            if (Auth::id() !== $message->user_id) {
                return $this->forbiddenResponse();
            }

            $message->delete();

            return $this->deletedResponse('Message deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting message with ID {$id}: " . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while deleting the message.', 500);
        }
    }
}
