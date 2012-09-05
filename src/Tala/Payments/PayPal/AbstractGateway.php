<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian.macneil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Payments\PayPal;

use Tala\Payments\Exception\InvalidResponseException;
use Tala\Payments\PayPal\Exception;
use Tala\Payments\PayPal\Response;
use Tala\Payments\Request;

/**
 * PayPal Base Class
 */
abstract class AbstractGateway extends \Tala\Payments\AbstractGateway
{
    protected $endpoint = 'https://api-3t.paypal.com/nvp';
    protected $testEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    protected $checkoutEndpoint = 'https://www.paypal.com/webscr';
    protected $testCheckoutEndpoint = 'https://www.sandbox.paypal.com/webscr';

    protected $username;
    protected $password;
    protected $signature;
    protected $testMode;

    public function getDefaultSettings()
    {
        return array(
            'username' => '',
            'password' => '',
            'signature' => '',
            'testMode' => false,
        );
    }

    public function capture(Request $request)
    {
        $data = $this->buildCapture($request);
        $response = $this->send($data);

        return new Response($response);
    }

    public function refund(Request $request)
    {
        $request = $this->_build_refund();
        $response = $this->send($request);

        return new Response($response);
    }

    protected function buildCapture(Request $request)
    {
        $request->validateRequiredParams(array('reference', 'amount'));

        $data = $this->buildRequest('DoCapture');
        $data['AMT'] = $request->getAmountDollars();
        $data['CURRENCYCODE'] = $request->getCurrency() ?: $this->getCurrency();
        $data['AUTHORIZATIONID'] = $request->getGatewayReference();
        $data['COMPLETETYPE'] = 'Complete';

        return $data;
    }

    protected function buildRefund(Request $request)
    {
        $request->validateRequiredParams(array('reference'));

        $data = $this->buildRequest('RefundTransaction');
        $data['TRANSACTIONID'] = $this->param('reference');
        $data['REFUNDTYPE'] = 'Full';

        return $data;
    }

    protected function buildRequest($method)
    {
        $data = array();
        $data['METHOD'] = $method;
        $data['VERSION'] = '85.0';
        $data['USER'] = $this->getUsername();
        $data['PWD'] = $this->getPassword();
        $data['SIGNATURE'] = $this->getSignature();

        return $data;
    }

    protected function buildPaymentRequest($request, $method, $action, $prefix = '')
    {
        $data = $this->buildRequest($method);

        $data[$prefix.'PAYMENTACTION'] = $action;
        $data[$prefix.'AMT'] = $request->getAmountDollars();
        $data[$prefix.'CURRENCYCODE'] = $request->getCurrency() ?: $this->getCurrency();
        $data[$prefix.'DESC'] = $request->getDescription();

        return $data;
    }

    /**
     * Post a request to the PayPal API and decode the response
     */
    protected function send($data)
    {
        // send and decode response
        $browser = new \Buzz\Browser();
        $response = $browser->get($this->getCurentEndpoint().'?'.http_build_query($data));

        $response_vars = array();
        parse_str($response->getContent(), $response_vars);

        // check whether response was successful
        if (isset($response_vars['ACK']) and in_array($response_vars['ACK'], array('Success', 'SuccessWithWarning'))) {
            return $response_vars;
        } elseif (isset($response_vars['L_LONGMESSAGE0'])) {
            throw new Exception($response_vars);
        }

        throw new InvalidResponseException();
    }

    protected function getCurentEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->endpoint;
    }

    protected function getCurrentCheckoutEndpoint()
    {
        return $this->getTestMode() ? $this->testCheckoutEndpoint : $this->checkoutEndpoint;
    }
}
