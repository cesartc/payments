<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/25/18
 * Time: 3:43 AM
 */

namespace App\Libraries\PaymentStrategies;

use App\Libraries\Payu\AuthAndCaptureResponse;
use App\Libraries\Payu\PayuRequestContainer;
use App\Libraries\Payu\AuthAndCaptureRequest;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Transaction;

class PayuPayment implements PaymentStrategy
{
    private $paymentCard;

    const VENDOR = 'PAYU';

    public function pay($order, $transaction, $buyer)
    {
        /* @var $request AuthAndCaptureRequest */
        $request = PayuRequestContainer::get(AuthAndCaptureRequest::class);

        $request->setOrderId($order->id);
        $request->setOrderDescription($order->id); // TODO define description
        $request->setAmount($order->getMoneyAmount());
        $request->setBuyerData($buyer);
        $request->setCardData($this->paymentCard);
        $request->setIpAddress($buyer->getIp());
        $request->setUserAgent($buyer->getAgent());

        return $request->execute();
    }

    /**
     * Creates the payment card based on the sent parameters
     *
     * @param $cardData array
     */
    public function setCardData($cardData)
    {
        $card = new PaymentCard();
        $card->setName($cardData['card_name']);
        $card->setDocType($cardData['card_doc_type']);
        $card->setDocNumber($cardData['card_doc_number']);
        $card->setNumber($cardData['card_number']);
        $card->setSecurityCode($cardData['card_security_code']);
        $card->setExpirationDate($cardData['card_expiration_date']);

        $this->paymentCard = $card;
    }

    /**
     * @inheritdoc
     */
    public function getVendor()
    {
        return self::VENDOR;
    }

}