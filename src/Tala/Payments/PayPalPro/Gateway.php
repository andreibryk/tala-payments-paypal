<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian.macneil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Payments\PayPalPro;

use Tala\Payments\PayPal\AbstractGateway;
use Tala\Payments\Request;
use Tala\Payments\PayPal\Response;

/**
 * PayPal Pro Class
 */
class Gateway extends AbstractGateway
{
    public function authorize(Request $request)
    {
        $data = $this->buildAuthorize($request, 'Authorization');
        $response = $this->send($data);

        return new Response($response);
    }

    public function purchase(Request $request)
    {
        $data = $this->buildAuthorize($request, 'Sale');
        $response = $this->send($data);

        return new Response($response);
    }

    protected function buildAuthorize($request, $action)
    {
        $card = $request->getSource();
        $card->validateNumber();
        $card->validateRequiredParams(array('number', 'firstName', 'lastName', 'expiryMonth', 'expiryYear', 'cvv'));

        $data = $this->buildPaymentRequest($request, 'DoDirectPayment', $action);

        // add credit card details
        $data['CREDITCARDTYPE'] = $card->getType();
        $data['ACCT'] = $card->getNumber();
        $data['EXPDATE'] = $card->getExpiryMonth().$card->getExpiryYear();
        $data['STARTDATE'] = $card->getStartMonth().$card->getStartYear();
        $data['CVV2'] = $card->getCvv();
        $data['ISSUENUMBER'] = $card->getIssue();
        $data['IPADDRESS'] = '';
        $data['FIRSTNAME'] = $card->getFirstName();
        $data['LASTNAME'] = $card->getLastName();
        $data['EMAIL'] = $card->getEmail();
        $data['STREET'] = $card->getAddress1();
        $data['STREET2'] = $card->getAddress2();
        $data['CITY'] = $card->getCity();
        $data['STATE'] = $card->getState();
        $data['ZIP'] = $card->getPostcode();
        $data['COUNTRYCODE'] = strtoupper($card->getCountry());

        return $data;
    }
}
