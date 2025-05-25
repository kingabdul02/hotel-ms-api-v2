<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function verifyPaystackPayment($transactionReference)
    {
        Log::alert('verifying...');
        $apiKey = 'pk_test_24673c9637a1bf06e5fb6eb989012747183eb2ae'; // Replace with your actual Paystack API key
        $url = 'https://api.paystack.co/transaction/verify/'.$transactionReference;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Accept' => 'application/json',
            ])->get($url);

            Log::alert('Done...');
            $statusCode = $response->status();
            $responseData = $response->json();
            Log::alert($responseData);

            return $responseData;
        } catch (\Exception $e) {
            // Handle exception, for example, log the error
            Log::alert('Exception occurred: '.$e->getMessage());
            // Handle exception logic here
        }

        return false;
    }
}
