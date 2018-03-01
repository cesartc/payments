<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/17/18
 * Time: 11:54 PM
 */

namespace App\Libraries\Payu;

use App\Exceptions\Payu\PayuAmountNotSetException;
use App\Exceptions\Payu\PayuResponseException;
use App\Models\PaymentCard;
use App\Models\User;

class AuthAndCaptureRequest extends PayuRequest
{
    protected $command = 'SUBMIT_TRANSACTION';

    protected $httpMethod = 'POST';

    protected function setUp()
    {
        $this->extraBody = [
            'transaction' => [
                'type' => 'AUTHORIZATION_AND_CAPTURE',
                'order' => [
                    'accountId' => env('PAYU_ACCOUNT_ID'),
                    'referenceCode' => null,
                    'description' => null,
                    'language' => env('PAYU_LANGUAGE'),
                    'signature' => null,
                    'additionalValues' => [
                        'TX_VALUE' => [
                            'value' => null,
                            'currency' => env('PAYU_CURRENCY')
                        ]
                    ],
                    'buyer' => [
                        'merchantBuyerId' => null,
                        'fullName' => null,
                        'emailAddress' => null,
                        'contactPhone' => null,
                        'dniNumber' => null
                    ],
                ],
                'payer' =>  [
                    'fullName' => null,
                    'dniType' => null,
                    'dniNumber' => null
                ],
                'creditCard' => [
                    'number' => null,
                    'securityCode' => null,
                    'expirationDate' => null,
                    'name' => null,
                ],
                'paymentMethod' => null,
                'paymentCountry' => env('PAYU_COUNTRY'),
                // TODO deviceSessionId assignment might need to be moved:
                'deviceSessionId' => md5(session_id() . microtime()),
                'ipAddress' => null,
                'userAgent' => null,
            ]
        ];
    }

    /**
     * Sets the order associated to the payment
     *
     * @param int $orderId ID of the order associated to the payment
     */
    public function setOrderId(int $orderId)
    {
        $this->extraBody['transaction']['order']['referenceCode'] = (string) $orderId;
    }

    /**
     * Sets the description of the associated order
     * @param string $orderDescription Description of the associated order
     */
    public function setOrderDescription(string $orderDescription)
    {
        $description = substr($orderDescription, 0, 255);
        $this->extraBody['transaction']['order']['description'] = $description;
    }

    /**
     * Sets the amount to be paid
     *
     * @param float $amount payment total amount
     */
    public function setAmount(float $amount)
    {
        $amount = strval($amount);
        $parts = explode('.', $amount);
        $amount = (isset($parts[0]) ? $parts[0] : '0') . '.';
        $amount .= (isset($parts[1]) ? substr($parts[1], 0, 2) : '0');

        $this->extraBody['transaction']['order']['additionalValues']['TX_VALUE']['value'] = $amount;
    }

    /**
     * Returns the transaction amount if it's set
     *
     * @return string
     * @throws PayuAmountNotSetException
     */
    public function getAmount()
    {
        if (! isset($this->extraBody['transaction']['order']['additionalValues']['TX_VALUE']['value']) ||
            is_null($this->extraBody['transaction']['order']['additionalValues']['TX_VALUE']['value'])
        ) {
            throw new PayuAmountNotSetException();
        }

        return $this->extraBody['transaction']['order']['additionalValues']['TX_VALUE']['value'];
    }

    /**
     * Sets the client related data for the request
     *
     * @param User $client
     */
    public function setBuyerData(User $client)
    {
        $this->extraBody['transaction']['order']['buyer']['merchantBuyerId'] = $client->id;
        $this->extraBody['transaction']['order']['buyer']['fullName'] = $client->getFullName() ;
        $this->extraBody['transaction']['order']['buyer']['emailAddress'] = $client->email;
        $this->extraBody['transaction']['order']['buyer']['contactPhone'] = $client->cellphone;
        $this->extraBody['transaction']['order']['buyer']['dniNumber'] = $client->doc_number;
    }

    /**
     * Sets the payment card related data for the request
     *
     * @param PaymentCard $card
     */
    public function setCardData(PaymentCard $card)
    {
        $this->extraBody['transaction']['payer']['fullName'] = $card->getName();
        $this->extraBody['transaction']['payer']['dniType'] = $card->getDocType();
        $this->extraBody['transaction']['payer']['dniNumber'] = $card->getDocNumber();

        $this->extraBody['transaction']['creditCard']['number'] = $card->getNumber();
        $this->extraBody['transaction']['creditCard']['securityCode'] = $card->getSecurityCode();
        $this->extraBody['transaction']['creditCard']['expirationDate'] = $card->getExpirationDate();
        $this->extraBody['transaction']['creditCard']['name'] = $card->getName();

        $this->extraBody['transaction']['paymentMethod'] = $card->getMethod();
    }

    /**
     * Sets the IP address for the request
     * @param string $ipAddress IP address of the client
     */
    public function setIpAddress(string $ipAddress)
    {
        $this->extraBody['transaction']['ipAddress'] = $ipAddress;
    }

    /**
     * Sets the user agent of the client.
     * example: Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0'
     *
     * @param string $userAgent the user agent of the client
     */
    public function setUserAgent(string $userAgent)
    {
        $this->extraBody['transaction']['userAgent'] = $userAgent;
    }

    public function parseResponse(array $response)
    {
        $authAndCaptureResponse = new AuthAndCaptureResponse();

        $authAndCaptureResponse->setCode($response['code']);
        $authAndCaptureResponse->setError($response['error']);

        if ($authAndCaptureResponse->isSuccess() &&
            ! empty($response['transactionResponse'])
        ) {
            $transaction = $response['transactionResponse'];

            $authAndCaptureResponse->setOrderId(
                isset($transaction['orderId']) ? $transaction['orderId'] : null
            );

            $authAndCaptureResponse->setTransactionId(
                isset($transaction['transactionId']) ? $transaction['transactionId'] : null
            );

            $authAndCaptureResponse->setTransactionState(
                isset($transaction['state']) ? $transaction['state'] : null
            );

            $authAndCaptureResponse->setTrazabilityCode(
                isset($transaction['trazabilityCode']) ? $transaction['trazabilityCode'] : null
            );

            $authAndCaptureResponse->setResponseCode(
                isset($transaction['responseCode']) ? $transaction['responseCode'] : null
            );

            $authAndCaptureResponse->setResponseMessage(
                isset($transaction['responseMessage']) ? $transaction['responseMessage'] : null
            );

        } else {
            throw new PayuResponseException($authAndCaptureResponse->getError());
        }

        return $authAndCaptureResponse;
    }
}
