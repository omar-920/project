<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Log;

class UserEmailVerification extends Controller
{
    use ApiResponseTrait;

    public function verifyNotice(Request $request)
    {
        try {
            $request->user()->sendEmailVerificationNotification();
            return $this->apiResponse("verify email", "The email should be verified to use the system");
        } catch (\Exception $e) {
            Log::error('Error sending email verification notification (verifyNotice): ' . $e->getMessage());
            return $this->serverErrorResponse('An error occurred while sending the verification email.', 500);
        }
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        try {
            $request->fulfill();
            return $this->apiResponse("Ok", "Verification done!");
        } catch (\Exception $e) {
            Log::error('Error fulfilling email verification (verifyEmail): ' . $e->getMessage());
            return $this->serverErrorResponse('An error occurred while verifying the email.', 500);
        }
    }

    public function verifyHandler(Request $request)
    {
        try {
            $request->user()->sendEmailVerificationNotification();
            return $this->apiResponse("ok", "Email verification link sent again!");
        } catch (\Exception $e) {
            Log::error('Error resending email verification link (verifyHandler): ' . $e->getMessage());
            return $this->serverErrorResponse('An error occurred while resending the verification email.', 500);
        }
    }
}
