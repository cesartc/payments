<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/26/18
 * Time: 11:15 PM
 */

namespace App\Validators;

use App\Models\Order;
use Illuminate\Support\Facades\Validator;

class OrderValidator
{
    public static function getOrderValidator(array $input)
    {
        $availablePaymentsString = Order::PAYMENT_METHOD_CARD . ',' .
            Order::PAYMENT_METHOD_DEPOSITO;

        $rules = [
            'payment_method' => 'required|in:' . $availablePaymentsString,
            'token' => 'required'
        ];

        return Validator::make($input, $rules);
    }
}
