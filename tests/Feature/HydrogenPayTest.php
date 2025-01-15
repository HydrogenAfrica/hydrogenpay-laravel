<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use HydrogenAfrica\Hydrogen\Hydrogen;

class HydrogenPayTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var Hydrogen */
    protected $hydrogen;

    /**
     * Set up the Hydrogen instance with mock configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock configuration for HydrogenPay. Provide your key to run the test case.
        config([
            'hydrogenpay.live_api_Key' => 'SK_LIVE_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'hydrogenpay.sandbox_Key' => 'PK_TEST_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'hydrogenpay.mode' => 'TEST',
        ]);

        // Initialize Hydrogen instance
        $this->hydrogen = new Hydrogen();
        
        // Debug: Print config to terminal
        dump(config('hydrogenpay'));
    }

    /**
     * Test if the payment reference is generated correctly.
     *
     * @test
     */
    public function it_generates_a_payment_reference()
    {
        // Generate payment reference
        $reference = $this->hydrogen->generateReference('TEST');

        // Log the generated reference for debugging
        Log::info('Generated Payment Reference:', ['reference' => $reference]);

        // Reference must start with 'TEST_'
        $this->assertStringStartsWith('TEST_', $reference);
    }

    /**
     * Test if payment initialization works correctly.
     *
     * @test
     */
    public function it_initializes_a_payment_successfully()
    {
        // Payment payload
        $paymentData = [
            "amount" => 20,
            "email" => "bwitlawalyusuf@gmail.com",
            "currency" => "NGN",
            "description" => "Demo Payment",
            "meta" => "Test Meta",
            "customerName" => "Lawal Yusuf",
            "callback" => "https://webhook-test.com/callback"
        ];

        // Initialize payment
        $response = $this->hydrogen->initializePayment($paymentData);

        // Log the response
        Log::info('Payment Initialization Response:', ['response' => $response]);
        dump('Payment Initialization Response:', $response);

        // Verify successful response
        $this->assertNotNull($response);
        $this->assertEquals('90000', $response['statusCode']);
    }

    /**
     * Test if payment verification works correctly.
     *
     * @test
     */
    public function it_verifies_a_payment_successfully()
    {
        // Transaction reference to verify
        $tranRef = "36934683_4714036ff9";

        // Verify the transaction
        $response = $this->hydrogen->verifyTransaction($tranRef);

        // Log the response
        Log::info('Payment Verification Response:', ['response' => $response]);
        dump('Payment Verification Response:', $response);

        // Verify successful transaction
        $this->assertNotNull($response);
        $this->assertEquals('90000', $response['statusCode']);
        $this->assertEquals('Operation Successful', $response['message']);
        $this->assertEquals('Paid', $response['data']['status']);
    }

    /**
     * Test if bank transfer is initiated successfully.
     *
     * @test
     */
    public function it_initiates_a_bank_transfer_successfully()
    {
        // Bank transfer data
        $data = [
            "amount" => 35,
            "email" => "bwitlawalyusuf@gmail.com",
            "currency" => "NGN",
            "description" => "Demo Transfer",
            "meta" => "Transfer Test",
            "customerName" => "Lawal Yusuf",
            "callback" => "https://hydrogenpay.com",
        ];

        // Initiate bank transfer
        $response = $this->hydrogen->transfers()->initiate($data);

        // Log the response
        Log::info('Bank Transfer Response:', ['response' => $response]);
        dump('Bank Transfer Response:', $response);

        // Verify successful transfer initiation
        $this->assertNotNull($response);
        $this->assertEquals('90000', $response['statusCode']);
        $this->assertEquals('Initiate bank transfer successful', $response['message']);
    }

    /**
     * Test if bank transfer simulation works.
     *
     * @test
     */
    public function it_simulates_a_bank_transfer_successfully()
    {
        // Data for simulation
        $data = [
            "clientTransactionRef" => "36934683_4714036ff9",
            "currency" => "NGN",
            "amount" => "30"
        ];

        // Simulate bank transfer
        $response = $this->hydrogen->transfers()->simulate($data);

        // Log the response
        Log::info('Simulate Bank Transfer Response:', ['response' => $response]);
        dump('Simulate Bank Transfer Response:', $response);

        // Verify successful simulation
        $this->assertNotNull($response);
        $this->assertEquals('90000', $response['statusCode']);
        $this->assertEquals('Operation Successful', $response['message']);
    }
}