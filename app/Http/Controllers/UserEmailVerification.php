<?php

namespace App\Http\Controllers;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


class UserEmailVerification extends Controller
{
    use ApiResponseTrait;

    public function verifyNotice(Request $request){
        $request->user()->sendEmailVerificationNotification();
        return $this->apiResponse("verify email","The email should be verified to use the system");

    }
    public function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();
        return $this->apiResponse("Ok","verification done!");
    }

    public function verifyHandler(Request $request){
        $request->user()->sendEmailVerificationNotification();
        return $this->apiResponse("ok","Email Verification link sent again!");
    }
}
