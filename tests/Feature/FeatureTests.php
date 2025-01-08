<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Stubs\Request;
use HydrogenAfrica\Hydrogen\Hydrogen;
use Tests\Stubs\PaymentEventHandler;
use Tests\Concerns\ExtractProperties;

class FeatureTests extends TestCase {

    use ExtractProperties;

    /**
     * Test if parameters are set on setData.
     *
     * @test
     * @return void
     */
    function getParams () {

        $request = new Request();
        $request->subaccounts = [];
        $request->meta = [];
        $request->ref = false;
        $request->logo = false;
        $request->title = false;
        $request->paymentplan = false;
        $request->phonenumber = '080232382382';
        $request->payment_method = 'online';
        $request->pay_button_text = 'Pay Now';
        $hydrogen = new Hydrogen();
        $hydrogen->initialize("http://localhost");

        $this->assertTrue($hydrogen instanceof Hydrogen);

        return $hydrogen;
    }

    /**
     * Test if hash is created.
     *
     * @test
     * @depends getParams
     * @param Hydrogen $hydrogen
     * @return void
     * @throws \ReflectionException
     */
    function creatingCheckSum(Hydrogen $hydrogen) {

        $publicKey = "FLWPUBK-MOCK-1cf610974690c2560cb4c36f4921244a-X";
        $hydrogen->initialize("http://localhost");
        $hydrogen = $hydrogen->createCheckSum('http://localhost');

        $hash = $this->extractProperty($hydrogen, "integrityHash");

        $this->assertEquals(64, strlen($hash["value"]));

        return $hydrogen;

    }

    /**
     * Testing payment.
     *
     * @test
     * @depends creatingCheckSum
     * @param Hydrogen $hydrogen
     * @return void
     */
    function paymentInitialize(Hydrogen $hydrogen) {

        $response = $hydrogen->eventHandler(new PaymentEventHandler)->initialize("http://localhost");

        $values = json_decode($response, true);

        $class = $this->data["class"];

        $this->assertArrayHasKey("meta", $values);
        $this->assertArrayHasKey("txref", $values);
        $this->assertArrayHasKey("amount", $values);
        $this->assertArrayHasKey("country", $values);
        $this->assertArrayHasKey("currency", $values);
        $this->assertArrayHasKey("PBFPubKey", $values);
        $this->assertArrayHasKey("custom_logo", $values);
        $this->assertArrayHasKey("redirect_url", $values);
        $this->assertArrayHasKey("data-integrity_hash", $values);
        $this->assertArrayHasKey("payment_method", $values);
        $this->assertArrayHasKey("customer_phone", $values);
        $this->assertArrayHasKey("customer_email", $values);
        $this->assertArrayHasKey("pay_button_text", $values);
        $this->assertArrayHasKey("customer_lastname", $values);
        $this->assertArrayHasKey("custom_description", $values);
        $this->assertArrayHasKey("customer_firstname", $values);
    }

    /**
     * Test if proper actions are taken when payment is cancelled.
     *
     * @test
     * @return void
     */
    function paymentCancelledTest() {
        $request = new Request();
        $request->cancelled = true;
        $hydrogen = new Hydrogen();
        $hydrogen = $hydrogen->createReferenceNumber();
        $ref = $hydrogen->getReferenceNumber();

        // This section tests if json is returned when no handler is set.

        $returned = $hydrogen->paymentCanceled($ref);

        $this->assertTrue( is_object($returned));

        // Tests if json has certain keys when payment is cancelled.

        $returned = json_decode(json_encode($returned), true);

        $this->assertArrayHasKey("data", $returned);
        $this->assertArrayHasKey("txRef", $returned['data']);
        $this->assertArrayHasKey("status", $returned['data']);

        // This section tests if instance of hydrogen is returned when a handler is set.
        $hydrogen->eventHandler(new PaymentEventHandler)->paymentCanceled($ref);

        $this->assertEquals(Hydrogen::class, get_class($hydrogen));

        return $ref;
    }

   
    function providesResponse () {

        return [
            [
                [
                    "body" => [
                        "status" => "unknown",
                        "data" => ["status", "unknown"]
                    ],
                ],
            ],
            [
                [
                    "body" => [
                        "status" => "success",
                    ],
                ]
            ],
            [
                [
                    "body" => [
                        "status" => "success",
                        "data" => [
                            "status" => "failed"
                        ]
                    ],
                ]
            ],
            [
                [
                    "body" => [
                        "status" => "success",
                        "data" => [
                            "status" => "successful"
                        ]
                    ],
                ]
            ]
        ];
    }
}
