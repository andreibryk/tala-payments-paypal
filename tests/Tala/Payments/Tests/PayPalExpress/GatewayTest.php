<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian.macneil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Payments\Tests\PayPalExpress;

use Tala\Payments\CreditCard;
use Tala\Payments\PayPalExpress\Gateway;
use Tala\Payments\Request;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->gateway = new Gateway(array(
            'username' => getenv('GATEWAY_USERNAME'),
            'password' => getenv('GATEWAY_PASSWORD'),
            'signature' => getenv('GATEWAY_SIGNATURE'),
            'testMode' => (bool) getenv('GATEWAY_TEST_MODE'),
        ));

        $this->card = new CreditCard(array(
            'firstName' => 'Example',
            'lastName' => 'User',
            'number' => getenv('CARD_NUMBER'),
            'expiryMonth' => getenv('CARD_EXP_MONTH'),
            'expiryYear' => getenv('CARD_EXP_YEAR'),
            'cvv' => getenv('CARD_CVV'),
        ));
    }

    /**
     * @group remote
     */
    public function testAuthorizeRemote()
    {
        $request = new Request();
        $request->amount = 1000;
        $request->cancelUrl = 'https://www.example.com/checkout';
        $request->returnUrl = 'https://www.example.com/complete';
        $response = $this->gateway->authorize($request, $this->card);

        $this->assertInstanceOf('\Tala\Payments\RedirectResponse', $response);
        $this->assertNotEmpty($response->getRedirectUrl());
    }
}
