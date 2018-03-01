<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/23/18
 * Time: 5:15 PM
 */

namespace tests;

use App\Models\PaymentCard;

class PaymentCardTest extends \TestCase
{
    public function testPaymentCardRecognizesVisa()
    {
        $visaNumber = "4907840000000005";
        $card = new PaymentCard();
        $card->setNumber($visaNumber);

        $this->assertEquals(
            PaymentCard::METHOD_VISA,
            $card->getMethod()
        );
    }

    public function testPaymentCardRecognizesMastercard()
    {
        $mastercardNumber = "5294191292751899";
        $card = new PaymentCard();
        $card->setNumber($mastercardNumber);

        $this->assertEquals(
            PaymentCard::METHOD_MASTERCARD,
            $card->getMethod()
        );
    }

    public function testPaymentCardRecognizesDiners()
    {
        $dinersNumber = "30535089346588";
        $card = new PaymentCard();
        $card->setNumber($dinersNumber);

        $this->assertEquals(
            PaymentCard::METHOD_DINERS,
            $card->getMethod()
        );
    }

    public function testPaymentCardRecognizesAmex()
    {
        $amexCard = "377753000000009";
        $card = new PaymentCard();
        $card->setNumber($amexCard);

        $this->assertEquals(
            PaymentCard::METHOD_AMEX,
            $card->getMethod()
        );
    }
}
