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
 * @author HydrogenAfrica <https://hydrogenpay.com>
 * @version 1.0
 */

class Transfers
{
    protected $live_api_Key;
    protected $sandbox_Key;
    protected $apiKey;
    protected $baseUrl;
    protected $endpoints;

    /**
     * Construct
     */
    function __construct(String $live_api_Key, String $sandbox_Key, String $baseUrl, String $apiKey)
    {

        $this->live_api_Key = $live_api_Key;
        $this->sandbox_Key = $sandbox_Key;
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;

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
        // Endpoint
        $endpoint = $this->endpoints['transfer']['initiate'];
        $url = "{$this->baseUrl}/{$endpoint}";

        // Log for debugging
        Log::info('Verifying payment with payload:', ['url' => $url, 'data' => $data]);

        try {
            // Send the POST request
            $response = Http::withToken($this->apiKey)->post($url, $data);

            // Parse and log the response
            $responseData = $response->json();
            Log::info('Payment transfer response:', $responseData);

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
        // Endpoint
        $endpoint = $this->endpoints['transfer']['simulate'];
        $url = "{$this->baseUrl}/{$endpoint}";

        Log::info('Verifying payment with payload:', ['url' => $url, 'data' => $data]);

        try {
            // Custom header
            $headers = [
                'Mode' => '19289182',
            ];

            $response = Http::withToken($this->apiKey)->withHeaders($headers)->post($url, $data);

            // Parse and log the response
            $responseData = $response->json();
            Log::info('Simulate Bank Transfer Response:', $responseData);

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
