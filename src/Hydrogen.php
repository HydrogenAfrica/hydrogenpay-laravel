<?php

namespace HydrogenAfrica\Hydrogen;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use HydrogenAfrica\Hydrogen\Helpers\Transfers;

/**
 * HydrogenPay Laravel Package
 *
 * This package enables seamless payment processing, including card transactions
 * and account transfers, for quick and efficient service delivery.
 *
 * @author HydrogenAfrica <contact@hydrogenafrica.com>
 * @version 1.0
 */

class Hydrogen
{
    protected $live_api_Key;
    protected $sandbox_Key;
    protected $baseUrl;
    protected $mode;
    protected $endpoints;

    /**
     * Construct
     */
    function __construct()
    {
        $this->live_api_Key = config('hydrogenpay.live_api_Key');
        $this->sandbox_Key = config('hydrogenpay.sandbox_Key');
        $this->mode = config('hydrogenpay.mode');
        $this->baseUrl = 'https://api.hydrogenpay.com/';

        // API endpoint
        $this->endpoints = [
            'payment' => [
                'initiate' => 'bepay/api/v1/merchant/initiate-payment',
                'confirm'  => 'bepay/api/v1/Merchant/confirm-payment',
            ],
            'transfer' => [
                'initiate'   => 'bepay/api/v1/merchant/initiate-bank-transfer',
                'simulate'   => 'bepay/api/v1/payment/simulate-bank-transfer',
            ],
        ];
    }

    /**
     * Get API Key
     *
     * @param string $mode
     * @return string
     */
    private function getApiKey(): string
    {
        // Convert mode and determine the API key
        return strtolower($this->mode) === 'live' ? $this->live_api_Key : $this->sandbox_Key;
    }

    /**
     * Generates a unique reference
     * @param $transactionPrefix
     * @return string
     */

    public function generateReference(String $transactionPrefix = NULL)
    {
        if ($transactionPrefix) {
            return $transactionPrefix . '_' . uniqid(time());
        }
        return 'hpg_' . uniqid(time());
    }

    /**
     * Initializes a payment by sending a POST request to the API
     *
     * @param array $data
     * @return array|null
     */

    public function initializePayment(array $data)
    {
        $endpoint = $this->endpoints['payment']['initiate']; //endpoint mapping
        $url = "{$this->baseUrl}/{$endpoint}";

        // Fetch API key
        $apiKey = $this->getApiKey();

        Log::info('Initializing payment request', ['url' => $url, 'data' => $data]);

        try {
            // MPOST request
            $response = Http::withToken($apiKey)->post($url, $data);

            // Parse JSON response
            $payment = $response->json();

            // Parse and validate response
            $payment = $response->json();
            if (!$response->ok() || !isset($payment['statusCode'], $payment['data']['transactionRef'], $payment['data']['url'])) {
                Log::error('Invalid payment response structure', ['response' => $payment]);
                return null;
            }

            // Log parsed responss
            Log::info('Payment initialized successfully:', ['response' => $payment]);

            return $payment;
        } catch (\Exception $e) {
            Log::error('Error initializing payment', [
                'message' => $e->getMessage(),
                'url'     => $url,
                'data'    => $data,
            ]);

            return null;
        }
    }

    /**
     * Gets a transaction ref depending on the redirect structure
     * @return string
     */
    public function getTransactionRef()
    {
        // Retrieve TransactionRef
        $transactionRef = request()->query('TransactionRef');

        // Return a JSON error response
        if (!$transactionRef) {

            Log::warning('TransactionRef is missing in the callback request.', [
                'url' => request()->fullUrl(),
                'payload' => request()->all(),
            ]);
        }

        return $transactionRef;
    }

    /**
     * @param $id
     * @return object
     */

    public function verifyTransaction($id)
    {
        $endpoint = $this->endpoints['payment']['confirm'];
        $url = "{$this->baseUrl}/{$endpoint}";

        // Fetch API key
        $apiKey = $this->getApiKey();

        $data = [
            'transactionRef' => $id,
        ];
        // Log
        Log::info('Verifying payment with payload:', ['url' => $url, 'data' => $data]);

        try {
            // Send POST request
            $response = Http::withToken($apiKey)->post($url, $data);

            // log the response
            $responseData = $response->json();
            Log::info('Payment verification response:', $responseData);

            return $responseData;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error verifying payment:', [
                'message' => $e->getMessage(),
                'transactionRef' => $id,
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying the payment.',
            ];
        }
    }

    /**
     * Transfers
     * @return Transfers
     */
    public function transfers()
    {
        $transfers = new Transfers($this->live_api_Key, $this->sandbox_Key, $this->baseUrl, $this->getApiKey());
        return $transfers;
    }
}
