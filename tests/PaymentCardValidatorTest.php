<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/27/18
 * Time: 12:00 AM
 */

namespace tests;


use App\Models\Transaction;
use App\Validators\PaymentCardValidator;

class PaymentCardValidatorTest extends \TestCase
{
    public function testValidateCorrectInput()
    {
        $validator = new PaymentCardValidator();
        $input = [
            'card_name' => 'SOMEONE',
            'card_doc_type' => Transaction::DOC_TYPE_DNI,
            'card_doc_number' => '12345678',
            'card_number' => '92929292929292',
            'card_security_code' => '123',
            'card_expiration_date' => '2018/12'
        ];

        $validator = $validator->getPaymentCardValidator($input);

        $this->assertFalse($validator->fails());
    }

    public function testValidateIncorrectInput()
    {
        $validator = new PaymentCardValidator();
        $input = [
            'card_name' => 'SOMEONE',
            'card_doc_type' => Transaction::DOC_TYPE_RUC,
            'card_doc_number' => '12345678',
            'card_number' => '',
            'card_security_code' => '12',
            'card_expiration_date' => '201812'
        ];

        $validator = $validator->getPaymentCardValidator($input);

        $errors = $validator->errors()->getMessages();

        $this->assertArrayHasKey('card_number', $errors);
        $this->assertArrayHasKey('card_doc_number', $errors);
        $this->assertArrayHasKey('card_security_code', $errors);
        $this->assertArrayHasKey('card_expiration_date', $errors);
        $this->assertArrayNotHasKey('card_name', $errors);
    }
}