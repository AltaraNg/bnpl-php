<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use App\Services\SendSmsService;
use Illuminate\Http\Request;

class OtpController extends Controller
{

    public function __construct(public readonly SendSmsService $sendSmsService, public readonly OtpService $otpService)
    {
    }
    public function generateOtp(Request $request)
    {
        $this->validate($request, [
            'phone_number' => ['required', 'string', 'max:11']
        ]);
        $response  = $this->otpService->createOtp($request->input('phone_number'));
        if ($response->status == false) {
            return $this->respondError($response->message, 400);
        }
        $message = 'Your OTP is '. $response->otp . ' If you did not initiate this request. Please ignore';
        $response  = $this->sendSmsService->sendMessage($request->input('phone_number'), $message);
        if ($response === true) {
            return $this->respondSuccess([], 'Otp Sent Successfully');
        }
        return $this->respondError('Otp failed to send', 400);
    }

    public function validateOtp(Request $request)
    {
        $this->validate($request, [
            'phone_number' => ['required', 'string', 'max:11'],
            'otp' => ['required', 'string', 'min:6'],
        ]);

        $response  = $this->otpService->validate($request->input('phone_number'), $request->input('otp'));

        if ($response->status === true) {
            return $this->respondSuccess([], $response->message);
        }
        return $this->respondError($response->message, 400);
    }
}
