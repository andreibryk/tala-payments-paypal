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

/**
 * PayPal Express Class
 */
class Response extends \Tala\Payments\Response
{

    public function __construct($responseData)
    {
        // find the reference
        $gatewayReference = null;
        foreach (array('REFUNDTRANSACTIONID', 'TRANSACTIONID', 'PAYMENTINFO_0_TRANSACTIONID') as $key) {
            if (isset($responseData[$key])) {
                $gatewayReference = $responseData[$key];
                break;
            }
        }

        parent::__construct($gatewayReference);
    }
}
