<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/25/18
 * Time: 3:43 AM
 */

namespace App\Libraries\PaymentStrategies;

class PagoefectivoPayment implements PaymentStrategy
{
    const VENDOR = 'PAGOEFECTIVO';

    public function pay($order, $transaction, $buyer)
    {
        // TODO: Implement pay() method.
    }

    public function getVendor()
    {
        return self::VENDOR;
    }
}