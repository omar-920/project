<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;

class AdminMessageController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $messages = Message::with('user:id,name')
                            ->select('id', 'subject', 'created_at', 'type')
                            ->latest()
                            ->paginate(10);
            return $this->retrievedResponse($messages);
        } catch (\Exception $e) {
            Log::error('Error retrieving admin messages: ' . $e->getMessage());
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
            $message->load('user:id,name,email');
            return $this->retrievedResponse($message);
        } catch (\Exception $e) {
            Log::error("Error retrieving message with ID {$id} for admin: " . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while retrieving the message.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $message = Message::find($id);
            if (!$message) {
                return $this->notFoundResponse();
            }
            $message->delete();
            return $this->deletedResponse('Message deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting message with ID {$id} for admin: " . $e->getMessage());
            return $this->serverErrorResponse('An unexpected error occurred while deleting the message.', 500);
        }
    }
}
