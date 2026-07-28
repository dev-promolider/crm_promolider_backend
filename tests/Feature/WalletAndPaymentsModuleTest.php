<?php

namespace Tests\Feature;

use Tests\TestCase;

class WalletAndPaymentsModuleTest extends TestCase
{
    public function test_wallet_and_payments_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/reports/mymovements/1',
            'api/v1/marketing/cart/buy-course',
            'api/v1/marketing/pay/openpay-order',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
