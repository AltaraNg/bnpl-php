<?php

namespace App\Services;

use th;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Exceptions\SmsMessageFailedToSendException;

class SendSmsService
{


    public function sendMessage(string $phone_number, string $message)
    {
        try {
            $response =  Http::withHeaders([
                'BNLP-ADMIN-ACCESS' => env('BNLP_ADMIN_ACCESS'),
            ])->post(env('ALTARA_PORTAL_BASE_URL') . '/bnlp/send/message', [
                'phone_number' => $this->appendPrefix($phone_number),
                'message' => $message,
            ]);
            $statusName = $response->json()['data']['response']['messages'][0]['status']['groupName'];
            $statusDescription = $response->json()['data']['response']['messages'][0]['status']['description'];
            if ($statusName !== 'Success' || $statusDescription != "Successful, Message was sent") {
                throw new SmsMessageFailedToSendException($statusDescription);
            }
            return true;
        } catch (\Throwable $th) {
            Log::error($th);
        }
        return false;
    }

    private function appendPrefix(string $number)
    {
        if (!$number) return '';
        $pre = '234';
        if ($number[0] == 0) {
            return $pre . substr($number, 1);
        } elseif (substr($number, 0, 3) == $pre) {
            return $number;
        }
        return $pre . $number;
    }

   
}