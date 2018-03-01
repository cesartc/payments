<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/26/18
 * Time: 11:38 PM
 */

namespace App\Validators;

use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class PaymentCardValidator
{
    public static function getPaymentCardValidator($input)
    {
        $rules = [
            'card_name' => 'required',
            'card_doc_type' => 'required|in:' . Transaction::DOC_TYPE_DNI . ',' . Transaction::DOC_TYPE_RUC,
            'card_number' => 'required',
            'card_security_code' => 'required|size:3',
            'card_expiration_date' => 'required|size:7', //2018/02
        ];

        $documentLength = '0';
        if (array_key_exists('card_doc_type', $input)) {
            if ($input['card_doc_type'] == Transaction::DOC_TYPE_DNI) {
                $documentLength = '8';

            } elseif ($input['card_doc_type'] == Transaction::DOC_TYPE_RUC) {
                $documentLength = '11';
            }
        }

        $rules['card_doc_number'] = 'required|digits:' . $documentLength;

        return Validator::make($input, $rules);
    }
}