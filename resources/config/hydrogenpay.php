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
     * Public Key: Your Hydrogen publicKey. Sign up on https://dashboard.hydrogenpay.com/ to get one from your settings page
     *
     */
    'publicKey' => env('PUBLIC_KEY'),

    /**
     * Secret Key: Your Hydrogen secretKey. Sign up on https://dashboard.hydrogenpay.com/ to get one from your settings page
     *
     */
    'secretKey' => env('SECRET_KEY'),

    /**
     * Prefix: Secret hash for webhook
     *
     */
    'secretHash' => env('SECRET_HASH', ''),
];
