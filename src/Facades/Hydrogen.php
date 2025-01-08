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


namespace HydrogenAfrica\Hydrogen\Facades;

use Illuminate\Support\Facades\Facade;

class Hydrogen extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hydrogenpay-laravel';
    }
}
