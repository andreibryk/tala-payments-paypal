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
        $request->validateRequired(array('amount', 'source'));

        $card = $request->source;
        $card->validateNumber;
        $card->validateRequired(array('number', 'firstName', 'lastName', 'expiryMonth', 'expiryYear', 'cvv'));

        $data = $this->buildPaymentRequest($request, 'DoDirectPayment', $action);

        // add credit card details
        $data['CREDITCARDTYPE'] = $card->type;
        $data['ACCT'] = $card->number;
        $data['EXPDATE'] = $card->expiryMonth.$card->expiryYear;
        $data['STARTDATE'] = $card->startMonth.$card->startYear;
        $data['CVV2'] = $card->cvv;
        $data['ISSUENUMBER'] = $card->issue;
        $data['IPADDRESS'] = '';
        $data['FIRSTNAME'] = $card->firstName;
        $data['LASTNAME'] = $card->lastName;
        $data['EMAIL'] = $card->email;
        $data['STREET'] = $card->address1;
        $data['STREET2'] = $card->address2;
        $data['CITY'] = $card->city;
        $data['STATE'] = $card->state;
        $data['ZIP'] = $card->postcode;
        $data['COUNTRYCODE'] = strtoupper($card->country);

        return $data;
    }
}
