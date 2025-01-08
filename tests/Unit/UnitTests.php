<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use ReflectionClass;
use ReflectionProperty;
use HydrogenAfrica\Hydrogen\Hydrogen;
use Tests\Stubs\PaymentEventHandler;
use Tests\Concerns\ExtractProperties;

class UnitTests extends TestCase
{

    use ExtractProperties;

    /**
     * Tests if app returns \HydrogenAfrica\Hydrogen\Hydrogen if called with ailas.
     *
     * @test
     * @return \HydrogenAfrica\Hydrogen\Hydrogen
     */
    function initiateHydrogenFromApp()
    {

        $hydrogen = $this->app->make("hydrogenpay-laravel");

        $this->assertTrue($hydrogen instanceof Hydrogen);

        return $hydrogen;
    }

    /**
     * Test Hydrogen initiallizes with default values;.
     *
     * @test
     *
     * @depends initiateHydrogenFromApp
     * @param \HydrogenAfrica\Hydrogen\Hydrogen $hydrogen
     * @return void
     * @throws \ReflectionException
     */
    function initializeWithDefaultValues(Hydrogen $hydrogen)
    {

        $reflector = new ReflectionClass($hydrogen);

        $methods = $reflector->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($methods as $method) {
            if ($method->getName() == 'baseUrl') $baseUrl = $method;
            if ($method->getName() == 'secretKey') $secretKey = $method;
            if ($method->getName() == 'publicKey') $publicKey = $method;
        };

        $baseUrl->setAccessible(true);
        $publicKey->setAccessible(true);
        $secretKey->setAccessible(true);

        $this->assertEquals($this->app->config->get("hydrogenpay.secretKey"), $secretKey->getValue($hydrogen));
        $this->assertEquals($this->app->config->get("hydrogenpay.publicKey"), $publicKey->getValue($hydrogen));
        $this->assertEquals(
            "https://api.hydrogenpay.com/v3",
            $baseUrl->getValue($hydrogen)
        );
    }

    /**
     * Tests if transaction reference is generated.
     *
     * @test
     * @param Hydrogen $hydrogen
     * @return void
     */
    function generateReference(Hydrogen $hydrogen)
    {

        $ref = $hydrogen->generateReference();

        $prefix = 'flw';

        $this->assertRegExp("/^{$prefix}_\w{13}$/", $ref);
    }

    /**
     * Testing if keys are modified using setkeys.
     *
     * @test
     * @depends initiateHydrogenFromApp
     * @param Hydrogen $hydrogen
     * @return void
     * @throws \ReflectionException
     */
    function settingKeys(Hydrogen $hydrogen)
    {

        $newPublicKey = "public_key";
        $newSecretKey = "secret_key";
        $hydrogen->setKeys($newPublicKey, $newSecretKey);
        $reflector = new ReflectionClass($hydrogen);
        $reflector = $reflector->getProperties(ReflectionProperty::IS_PROTECTED);

        $keys = array_map(function ($value) use ($hydrogen, $newPublicKey, $newSecretKey) {
            $name = $value->getName();
            if ($name === "publicKey" || $name === "secretKey") {
                $value->setAccessible(true);
                $key = $value->getValue($hydrogen);
                $this->assertEquals(${"new" . ucfirst($name)}, $key);
            }
        }, $reflector);
    }
}
