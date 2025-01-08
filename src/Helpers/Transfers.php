<?php

namespace HydrogenAfrica\Hydrogen\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * HydrogenPay Laravel Package
 *
 * This package enables seamless payment processing, including card transactions
 * and account transfers, for quick and efficient service delivery.
 *
 * @author HydrogenAfrica <contact@hydrogenafrica.com>
 * @version 1.0
 */

class Transfers
{

    protected $publicKey;
    protected $secretKey;
    protected $baseUrl;
    protected $endpoints;


    /**
     * Construct
     */
    function __construct(String $publicKey, String $secretKey, String $baseUrl)
    {

        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
        $this->baseUrl = $baseUrl;
        $this->endpoints = [
            'transfer' => [
                'initiate'   => 'bepay/api/v1/merchant/initiate-bank-transfer',
                'simulate'   => 'bepay/api/v1/payment/simulate-bank-transfer',
            ],
        ];
    }


    /**
     * Initiate a bank transfer
     * @param $data
     * @return object
     */

    public function initiate(array $data)
    {
        // Endpoint mapping
        $endpoint = $this->endpoints['transfer']['initiate'];
        $url = "{$this->baseUrl}/{$endpoint}";

        // Log the data for debugging
        Log::info('Verifying payment with payload:', ['url' => $url, 'data' => $data]);

        try {
            // Send the POST request with the correct data
            $response = Http::withToken($this->secretKey)->post($url, $data);

            // Parse and log the response
            $responseData = $response->json();
            Log::info('Payment transfer response:', $responseData);

            // dd($responseData);

            return $responseData;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Transfer Error:', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while transfering.',
            ];
        }
    }


    /**
     * simulate a bank transfer
     * @param $data
     * @return object
     */
    public function simulate(array $data)
    {
        // Endpoint mapping
        $endpoint = $this->endpoints['transfer']['simulate'];
        $url = "{$this->baseUrl}/{$endpoint}";

        // Log the data for debugging
        Log::info('Verifying payment with payload:', ['url' => $url, 'data' => $data]);

        try {
            // Set the custom header
            $headers = [
                'Mode' => '19289182',
            ];

            // Send the POST request with the correct data
            $response = Http::withToken($this->secretKey)->withHeaders($headers)->post($url, $data);

            // Parse and log the response
            $responseData = $response->json();
            Log::info('Simulate Bank Transfer Response:', $responseData);

            // dd($responseData);

            return $responseData;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Transfer Error:', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while transfering.',
            ];
        }
    }
}
