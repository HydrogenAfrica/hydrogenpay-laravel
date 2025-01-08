<?php

namespace Tests\Unit;

use Tests\TestCase;
use HydrogenAfrica\Hydrogen\Hydrogen;

class HydrogenServiceProviderTests extends TestCase
{
    /**
     * Tests if service provider Binds alias "hydrogenpay-laravel" to \HydrogenAfrica\Hydrogen\Hydrogen
     *
     * @test
     */
    public function isBound()
    {
        $this->assertTrue($this->app->bound('hydrogenpay-laravel'));
    }
    /**
     * Test if service provider returns \Hydrogen as alias for \HydrogenAfrica\Hydrogen\Hydrogen
     *
     * @test
     */
    public function hasAliased()
    {
        $this->assertTrue($this->app->isAlias("HydrogenAfrica\Hydrogen\Hydrogen"));
        $this->assertEquals('hydrogenpay-laravel', $this->app->getAlias("HydrogenAfrica\Hydrogen\Hydrogen"));
    }
}
