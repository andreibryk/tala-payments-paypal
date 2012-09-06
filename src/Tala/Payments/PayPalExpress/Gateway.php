<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian.macneil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Payments\PayPalExpress;

use Tala\Payments\PayPal\AbstractGateway;
use Tala\Payments\PayPal\Response;
use Tala\Payments\RedirectResponse;
use Tala\Payments\Request;

/**
 * PayPal Express Class
 */
class Gateway extends AbstractGateway
{
    protected $solutionType;
    protected $landingPage;

    public function getDefaultSettings()
    {
        $settings = parent::getDefaultSettings();
        $settings['solutionType'] = array('Sole', 'Mark');
        $settings['landingPage'] = array('Billing', 'Login');

        return $settings;
    }

    public function authorize(Request $request)
    {
        $data = $this->buildAuthorize($request);
        $response = $this->send($data);

        return new RedirectResponse($this->getCurrentCheckoutEndpoint().'?'.http_build_query(array(
            'cmd' => '_express-checkout',
            'useraction' => 'commit',
            'token' => $response['TOKEN'],
        )));
    }

    public function completeAuthorize(Request $request)
    {
        $data = $this->confirmReturn($request, 'Authorization');

        return new Response($data);
    }

    public function purchase(Request $request)
    {
        // authorize first then process as 'Sale' in DoExpressCheckoutPayment
        $this->authorize($request);
    }

    public function completePurchase(Request $request)
    {
        $data = $this->confirmReturn($request, 'Sale');

        return new Response($data);
    }

    protected function buildAuthorize(Request $request)
    {
        $request->validateRequired(array('returnUrl', 'cancelUrl'));

        $prefix = 'PAYMENTREQUEST_0_';
        $data = $this->buildPaymentRequest($request, 'SetExpressCheckout', 'Authorization', $prefix);

        // pp express specific fields
        $data['SOLUTIONTYPE'] = $this->getSolutionType();
        $data['LANDINGPAGE'] = $this->getLandingPage();
        $data['NOSHIPPING'] = 1;
        $data['ALLOWNOTE'] = 0;
        $data['RETURNURL'] = $request->returnUrl;
        $data['CANCELURL'] = $request->cancelUrl;

        $card = $request->source;
        $data[$prefix.'SHIPTONAME'] = $card->name;
        $data[$prefix.'SHIPTOSTREET'] = $card->address1;
        $data[$prefix.'SHIPTOSTREET2'] = $card->address2;
        $data[$prefix.'SHIPTOCITY'] = $card->city;
        $data[$prefix.'SHIPTOSTATE'] = $card->state;
        $data[$prefix.'SHIPTOCOUNTRYCODE'] = $card->country;
        $data[$prefix.'SHIPTOZIP'] = $card->postcode;
        $data[$prefix.'SHIPTOPHONENUM'] = $card->phone;
        $data['EMAIL'] = $card->email;

        return $data;
    }

    protected function confirmReturn($request, $action)
    {
        $prefix = 'PAYMENTREQUEST_0_';
        $data = $this->buildPaymentRequest($request, 'DoExpressCheckoutPayment', $action, $prefix);

        $data['TOKEN'] = isset($_POST['token']) ? $_POST['token'] : '';
        $data['PAYERID'] = isset($_POST['PayerID']) ? $_POST['PayerID'] : '';

        return $this->send($data);
    }
}
