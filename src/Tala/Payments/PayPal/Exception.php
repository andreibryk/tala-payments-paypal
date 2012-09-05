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
 * Invalid Response exception.
 *
 * Thrown when a gateway responded with invalid or unexpected data (for example, a security hash did not match).
 *
 * @author  Adrian Macneil <adrian.macneil@gmail.com>
 */
class Exception extends \RuntimeException implements \Tala\Payments\Exception
{
    public function __construct($responseData)
    {
        parent::__construct($responseData['L_LONGMESSAGE0'], $responseData['L_ERRORCODE0']);
    }
}
