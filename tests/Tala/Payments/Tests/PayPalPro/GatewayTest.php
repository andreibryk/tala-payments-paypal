<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian.macneil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Payments\Tests\PayPalPro;

use Tala\Payments\CreditCard;
use Tala\Payments\PayPalPro\Gateway;
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

        $this->request = new Request();
        $this->request->amount = 1000;
    }

    protected function getMockBrowser()
    {
        return $this->getMock('\Buzz\Browser');
    }

    protected function getMockResponse($message)
    {
        $response = $this->getMock('\Buzz\Message\Response');
        $response->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue($message));

        return $response;
    }

    public function testAuthorizeRequiresAmount()
    {
        $this->setExpectedException('\Tala\Payments\Exception\MissingParameterException', 'The amount parameter is required');

        $this->request->amount = 0;
        $response = $this->gateway->authorize($this->request, $this->card);
    }

    public function testAuthorize()
    {
        $mockBrowser = $this->getMockBrowser();
        $mockResponse = $this->getMockResponse('TIMESTAMP=2012%2d09%2d06T06%3a34%3a46Z&CORRELATIONID=1a0e1b3ba661b&ACK=Success&VERSION=85%2e0&BUILD=3587318&AMT=11%2e00&CURRENCYCODE=USD&AVSCODE=X&CVV2MATCH=M&TRANSACTIONID=7T274412RY6976239');

        $mockBrowser->expects($this->once())
             ->method('get')
             ->will($this->returnValue($mockResponse));

        $this->gateway->setBrowser($mockBrowser);
        $response = $this->gateway->authorize($this->request, $this->card);

        $this->assertInstanceOf('\Tala\Payments\ResponseInterface', $response);
        $this->assertEquals('7T274412RY6976239', $response->getGatewayReference());
    }

    /**
     * @group remote
     */
    public function testAuthorizeCaptureRemote()
    {
        $authRequest = new Request();
        $authRequest->amount = 1100;
        $authResponse = $this->gateway->authorize($authRequest, $this->card);

        $this->assertInstanceOf('\Tala\Payments\ResponseInterface', $authResponse);
        $this->assertNotEmpty($authResponse->getGatewayReference());

        $captureRequest = new Request();
        $captureRequest->gatewayReference = $authResponse->getGatewayReference();
        $captureRequest->amount = 1100;
        $captureResponse = $this->gateway->capture($captureRequest);

        $this->assertInstanceOf('\Tala\Payments\ResponseInterface', $captureResponse);
        $this->assertNotEmpty($captureResponse->getGatewayReference());
    }

    /**
     * @group remote
     */
    public function testPurchaseRemote()
    {
        $purchaseRequest = new Request();
        $purchaseRequest->amount = 1300;
        $purchaseResponse = $this->gateway->purchase($purchaseRequest, $this->card);

        $this->assertInstanceOf('\Tala\Payments\ResponseInterface', $purchaseResponse);
        $this->assertNotEmpty($purchaseResponse->getGatewayReference());
    }
}
