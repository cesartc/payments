<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/25/18
 * Time: 3:43 AM
 */

namespace App\Libraries\PaymentStrategies;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;

interface PaymentStrategy
{
    /**
     * Executes the payment process
     *
     * @param $order Order
     * @param $transaction Transaction
     * @param $buyer User
     *
     * @return PaymentResponse
     */
    public function pay($order, $transaction, $buyer);

    /**
     * Returns the payment vendor (Payu, PagoEfectivo, etc)
     *
     * @return string
     */
    public function getVendor();
}
