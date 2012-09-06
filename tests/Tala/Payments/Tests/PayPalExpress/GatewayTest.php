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
            'username' => getenv('PAYPAL_USERNAME'),
            'password' => getenv('PAYPAL_PASSWORD'),
            'signature' => getenv('PAYPAL_SIGNATURE'),
            'testMode' => (bool) getenv('PAYPAL_TEST_MODE'),
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
        $request->setSource($this->card);
        $request->setAmount(1000);
        $request->setCancelUrl('https://www.example.com/checkout');
        $request->setReturnUrl('https://www.example.com/complete');
        $response = $this->gateway->authorize($request);

        $this->assertInstanceOf('\Tala\Payments\RedirectResponse', $response);
        $this->assertNotEmpty($response->getRedirectUrl());
    }
}
