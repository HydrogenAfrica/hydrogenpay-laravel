<?php

/*
 * This file is part of the HydrogenPay Laravel package.
 *
 * The HydrogenPay Laravel package simplifies payment integration,
 * supporting seamless card transactions and account transfers
 * to ensure faster and more efficient delivery of goods and services.
 *
 * For full copyright and licensing details, please refer to the LICENSE
 * file included with this source code.
 */

return [
    /**
     * Live API Key: Your Hydrogen live API key.
     * Sign up on https://dashboard.hydrogenpay.com/signup to get one from your settings page.
     */
    'live_api_Key' => env('LIVE_API_KEY'),

    /**
     * Sandbox Key: Your Hydrogen sandbox API key.
     * Sign up on https://dashboard.hydrogenpay.com/signup to get one from your settings page.
     */
    'sandbox_Key' => env('SANDBOX_KEY'),

    /**
     * Mode: Set to 'live' or 'test' to toggle between live and sandbox environments.
     */
    'mode' => env('MODE', 'test'),
];
