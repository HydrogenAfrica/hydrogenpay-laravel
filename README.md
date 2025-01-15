# HydrogenPay Laravel Package

## Introduction

The HydrogenPay Laravel package enables seamless and secure payment processing through card transactions and account transfers, ensuring faster delivery of goods and services.

Easily integrate HydrogenPay APIs into your Laravel applications with a simplified and efficient setup for smooth payment operations.


Key features:

- Collections: Card, Transfers, Payments, Bank Transfers.
- Confirmation: Payment Confirmation.

## Table of Contents
1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Testing](#testing)
5. [Support](#Support)
6. [Contribution](#Contribution)
7. [License](#License)
8. [Hydrogenpay-API-References](#API-References)


## Requirements
1. PHP version 7.4, 8.0, 8.1, or 8.2.
2. Supported Laravel Versions: 7.x, 8.x, 9.x, 10.x, and 11.x
3. Composer must be installed.


## Installation
1. To install the package, run

```sh

composer require hydrogenafrica/hydrogenpay-laravel

```

OR

Add the following line to the require block of your composer.json file.

```sh

"hydrogenafrica/hydrogenpay-laravel": "1.0.*"

```

You'll then need to run composer install or composer update to download it and have the autoloader updated.

2. Make sure you register the service provider, Once HydrogenPay Laravel is installed. 
   Open up config/app.php and add the providers array.

```sh

HydrogenAfrica\Hydrogen\HydrogenServiceProvider::class,

```

3. Make sure you add HydrogenPay to the aliases

```sh

'HydrogenPay' => HydrogenAfrica\Hydrogen\Facades\Hydrogen::class

```

4. Publish the configuration file using this command:

```sh

php artisan vendor:publish --provider="HydrogenAfrica\Hydrogen\HydrogenServiceProvider"

```

A configuration-file named hydrogenpay.php will be placed in your config directory.

Get your keys from [here](https://dashboard.hydrogenpay.com)

Add the following environment variables to your .env file:

```sh
LIVE_API_KEY=SK_LIVE_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
SANDBOX_KEY=PK_TEST_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
MODE=TEST
```
- LIVE_API_KEY: Your HydrogenPay live API key obtained from the dashboard. (Required)
- SANDBOX_KEY: Your HydrogenPay sandbox API key obtained from the dashboard. (Required)
- MODE: Defines the environment mode. Set to LIVE to use the LIVE_API_KEY or TEST to use the SANDBOX_KEY. (Required)


# Usage
This documentation covers all components of the hydrogen_laravel SDK.
Set up routes, view and controller methods like so:

## ```1. Setup Routes```

```php

<?php

use App\Http\Controllers\HydrogenPayController;
use Illuminate\Support\Facades\Route;


// The home page that displays the payment form
Route::get('/', function () {
return view('home');
});

// The route that the button calls to initialize payment
Route::post('/pay', [HydrogenPayController::class, 'initialize'])->name('paynow');

// The callback url after a payment
Route::get('/trans/callback', [HydrogenPayController::class, 'handleCallback'])->name('callback');

```

## ```2. Setup the Payment Home Page(View)```

```php

<h3>Make a Payment</h3>

<form method="POST" action="{{ route('paynow') }}">
    {{ csrf_field() }}

    <input type="number" name="amount" placeholder="Amount"><br><br>

    <input type="text" name="name" placeholder="Full Name"><br><br>

    <input type="email" name="email" placeholder="Email Address"><br><br>

    <input type="text" name="description" placeholder="Payment Description"><br><br>

    <button type="submit">Pay Now</button>
</form>

```

## ```3. Setup your Controller (HydrogenPayController)```

```php

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use HydrogenAfrica\Hydrogen\Facades\Hydrogen as HydrogenPay;

class HydrogenPayController extends Controller
{
    /**
     * Initialize the payment process and redirects the user to the payment URL.
     * 
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function initialize()
    {
        // Payment details
        $data = [
            "amount" => request()->amount, // Payment amount
            "email" => request()->email,   // Customer's email address
            "currency" => "NGN",          // Currency 
            "description" => request()->description, // Payment description
            "customerName" => request()->name,      // Customer's full name
            "callback" => route('callback'),       // Callback URL after payment
        ];

        // Initialize payment using HydrogenPay
        $payment = HydrogenPay::initializePayment($data);

        if ($payment['statusCode'] !== '90000') {
            // Handle payment initialization failure
            return;
        }

        // Redirect user to payment URL
        return redirect($payment['data']['url']);
    }

    /**
     * Handle the payment callback.
     *
     * This allows businesses to verify the status of initiated payments using the transaction reference
     * Verifies the payment status and returns the appropriate response.
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCallback()
    {
        try {
            // Retrieve transaction reference
            $transactionRef = HydrogenPay::getTransactionRef();

            // Verify the transaction
            $data = HydrogenPay::verifyTransaction($transactionRef);

            // Check verification result
            if (isset($data['statusCode']) && $data['statusCode'] === '90000') {
                $status = $data['data']['transactionStatus'] ?? 'Cancelled';

                if ($status === 'Paid') {
                    // Payment successful
                    return response()->json(['success' => true, 'message' => 'Payment Successful.', 'data' => $data['data']]);
                } elseif ($status === 'Failed') {
                    // Payment failed
                    return response()->json(['success' => false, 'message' => 'Payment Failed.', 'data' => $data['data']]);
                } elseif ($status === 'Pending') {
                    // Payment pending/cancelled
                    return response()->json(['success' => false, 'message' => 'Payment Pending.', 'data' => $data['data']]);
                }
            }

            // Verification failed
            return response()->json(['success' => false, 'message' => 'Transaction verification failed.', 'data' => $data]);

        } catch (\Exception $e) {
            // Handle exceptions during callback processing
            return response()->json(['success' => false, 'message' => 'An error occurred while processing the callback.'], 500);
        }
    }

    /**
     * Handle fund transfer.
     * 
     * Generates dynamic virtual account details for completing payment transactions.
     * Initiates a bank transfer using HydrogenPay.
     * @return void
     */
    public function handleTransfer()
    {
        // Transfer details
        $data = [
            "amount" => request()->amount, // Transfer amount
            "email" => request()->email,   // Recipient's email address
            "currency" => "NGN",          // Currency
            "description" => request()->description, // Transfer description
            "meta" => request()->name,              // Other information
            "customerName" => request()->name,      // Customer's full name
            "callback" => route('callback'),       // Callback URL after transfer
        ];

        // Initiate transfer
        $transfer = HydrogenPay::transfers()->initiate($data);

        // Debug response
        dd($transfer);
    }

    /**
     * Simulate a transfer for testing purposes.
     * Simulate a Bank Transfer Transaction to test account transfer behavior for completing transactions.
     * @return void
     */
    public function handleSimulateTransfer()
    {
        // Simulation details
        $data = [
            "amount" => '30',            // Simulated amount
            "currency" => "NGN",         // Currency
            "clientTransactionRef" => request()->transactionref, // Transaction reference
        ];

        // Simulate transfer
        $simulatetransfer = HydrogenPay::transfers()->simulate($data);

        // Debug response
        dd($simulatetransfer);
    }

    /**
     * Handle webhook notifications.
     *
     * Logs webhook data for transaction updates.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        // Retrieve and log the webhook payload
        $body = $request->getContent();
        Log::info('Webhook received', ['body' => $body]);

        // Decode and log JSON data
        $data = json_decode($body, true);
        Log::info('Parsed webhook data', ['data' => $data]);

        // Respond with success
        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}


```

## Running HydrogenPay Tests

All SDK tests are implemented using Laravel's testing module. To run these tests effectively, follow the steps below:

Copy the ```HydrogenPayTest``` file from the SDK's tests folder to your Laravel application's tests/Feature folder:

```sh

Running the Tests

1. Run All Tests at Once:

    php artisan test

2. Run the HydrogenPay Test Specifically:

    php artisan test --filter HydrogenPayTest


"Payment Initialization Response:" // tests\Feature\HydrogenPayTest.php:80
array:3 [
  "statusCode" => "90000"
  "message" => "Initiate payment Saved successfully."
  "data" => array:2 [
    "transactionRef" => "36934683_830548604a"
    "url" => "https://payment.hydrogenpay.com?transactionId=747c0000-df77-2a32-a8b2-08dd357dd433&Mode=19289182"
  ]
] 

"Payment Verification Response:" // tests\Feature\HydrogenPayTest.php:102
array:3 [
  "statusCode" => "90000"
  "message" => "Operation Successful"
  "data" => array:21 [
    "id" => "d54f0000-73e7-b252-c8e7-08dd2f363209"
    "amount" => 30.0
    "chargedAmount" => 30.3
    "currency" => "NGN"
    "customerEmail" => "loyaglobaltech@gmail.com"
    "narration" => "Demo Transfer"
    "description" => "Demo Transfer"
    "status" => "Paid"
    "transactionStatus" => "Paid"
    "transactionRef" => "36934683_4714036ff9"
    "processorResponse" => null
    "createdAt" => "2025-01-07T16:13:17.0719962"
    "paidAt" => "2025-01-07T16:13:17.0719963"
    "ip" => null
    "paymentType" => "BankTransfer"
    "authorizationObject" => null
    "fees" => 0.3
    "vat" => 0.0
    "meta" => "Yemi"
    "recurringCardToken" => ""
    "metadata" => null
  ]
] 

"Bank Transfer Response:" // tests\Feature\HydrogenPayTest.php:134
array:3 [
  "statusCode" => "90000"
  "message" => "Initiate bank transfer successful"
  "data" => array:9 [
    "transactionRef" => "36934683_9904269434"
    "virtualAccountNo" => "1916691324"
    "virtualAccountName" => "HYDROGENPAY"
    "expiryDateTime" => "2025-01-15 17:31:13"
    "capturedDatetime" => null
    "completedDatetime" => null
    "transactionStatus" => "Pending"
    "amountPaid" => 35
    "bankName" => "Access Bank"
  ]
] 

"Simulate Bank Transfer Response:" // tests\Feature\HydrogenPayTest.php:161

array:3 [
  "statusCode" => "90000"
  "message" => "Operation Successful"
  "data" => array:32 [
    "orderId" => "36934683_4714036ff9"
    "merchantRef" => "36934683"
    "transactionId" => "d54f0000-73e7-b252-c8e7-08dd2f363209"
    "amount" => "30.00"
    "customerEmail" => "loyaglobaltech@gmail.com"
    "description" => "Demo Transfer"
    "merchantInfo" => null
    "currencyInfo" => null
    "canRetry" => null
    "timeoutLeft" => null
    "callBackUrl" => null
    "otpOrBankTransferTimeoutLeft" => null
    "isRecurring" => false
    "billingMessage" => null
    "isRecurring" => false
    "billingMessage" => null
    "frequency" => null
    "isRecurringActive" => null
    "serviceFees" => null
    "frequency" => null
    "isRecurringActive" => null
    "serviceFees" => null
    "isRecurringActive" => null
    "serviceFees" => null
    "totalAmount" => null
    "discountAmount" => null
    "paymentId" => "success-success-success-success-success-success-success-success-HYDR4e6ece8cd2e9e58a6a"
    "currency" => "NGN"
    "discountPercentage" => 0
    "isCardSpecificDiscount" => false
    "isBankDiscountEnabled" => false
    "bankDiscountValue" => null
    "bankDiscountedAmount" => null
    "vatFee" => null
    "vatPercentage" => 0
    "transactionMode" => 0
    "transactionType" => null
    "customerFeePercentage" => 0
    "merchantServiceFee" => null
  ]
]

```

## Support

For more assistance with this Package, reach out to the Developer Experience team via [email](mailto:support@hydrogenpay.com) or consult our documentation [here](https://docs.hydrogenpay.com/reference/api-authentication)


## Contribution

If you discover a bug or have a solution to improve the Hydrogen Payment Gateway for the WooCommerce plugin, we welcome your contributions to enhance the code.


Create a detailed bug report or feature request in the "Issues" section.

If you have a code improvement or bug fix, feel free to submit a pull request.

 * Fork the repository on GitHub

 * Clone the repository into your local system and create a branch that describes what you are working on by pre-fixing with feature-name.

 * Make the changes to your forked repository's branch. Ensure you are using PHP Coding Standards (PHPCS).

 * Make commits that are descriptive and breaks down the process for better understanding.

 * Push your fix to the remote version of your branch and create a PR that aims to merge that branch into master.
 
 * After you follow the step above, the next stage will be waiting on us to merge your Pull Request.


## License

By contributing to this library, you agree that your contributions will be licensed under its [MIT license](/LICENSE).
Copyright (c) Hydrogen.


## API-References

- [Hydrogenpay Dashboard](https://dashboard.hydrogenpay.com/merchant/profile/api-integration)
- [Hydrogenpay API Documentation](https://docs.hydrogenpay.com/reference/api-authentication)