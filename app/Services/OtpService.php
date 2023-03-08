<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OtpService
{

    private $validity = 30;
    private $deleteOldOtps = 30;

    public function createOtp($identifier)
    {

        $this->deleteOldOtps();
        $otp = Otp::where('identifier', $identifier)->first();
        if ($otp == null) {
            $otp = Otp::create([
                'identifier' => $identifier,
                'token' => $this->generateNumericOTP(6),
                'validity' => $this->validity,
                'generated_at' => Carbon::now(),
            ]);
        } else {
            if (!$otp->isExpired() && !request('regenerate')) {
                return (object)[
                    'status' => false,
                    'message' => 'Please check your message, you have an otp that expires in: ' . $otp->expiresIn(),
                ];
            }
            $otp->update([
                'identifier' => $identifier,
                'token' => $this->generateNumericOTP(6),
                'validity' => $this->validity,
                'generated_at' => Carbon::now(),
            ]);
        }
        return (object)[
            'status' => true,
            'otp' => $otp->token,
            'message' => "OTP generated",
            'expires_in' => $otp->expiresIn(),
        ];
    }


    public function validate(string $identifier, string $token): object
    {
        $otp = Otp::where('identifier', $identifier)->first();

        if (!$otp) {
            return (object)[
                'status' => false,
                'message' => 'Otp not found',
            ];
        }

        if ($otp->isExpired()) {
            return (object)[
                'status' => false,
                'message' => 'Invalid otp, please generate a new one',
            ];
        }

        if ($otp->token == $token) {
            $otp->expired = true;
            $otp->update();
            return (object)[
                'status' => true,
                'message' => 'OTP is valid',
            ];
        }
        return (object)[
            'status' => false,
            'message' => 'OTP does not match',
        ];
    }


    public function deleteOldOtps(): bool
    {
        // dd($this->deleteOldOtps);
        return Otp::where('expired', true)
            ->orWhere('created_at', '<', Carbon::now()->subMinutes($this->deleteOldOtps))
            ->delete();
    }

    // Function to generate OTP
    public function generateNumericOTP(int $n): string
    {

        // Take a generator string which consist of
        // all numeric digits
        $generator = "1357902468";

        // Iterate for n-times and pick a single character
        // from generator and append it to $result

        // Logic for generating a random character from generator
        //     ---generate a random number
        //     ---take modulus of same with length of generator (say i)
        //     ---append the character at place (i) from generator to result
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        // Return result
        return $result;
    }
}
