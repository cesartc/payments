<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/24/18
 * Time: 12:58 AM
 */

namespace App\Libraries\Payu;

use App\Libraries\PaymentStrategies\PaymentResponse;

class AuthAndCaptureResponse extends PayuResponse implements PaymentResponse
{
    const TRANSACTION_STATE_APPROVED = 'APPROVED';

    /**
     * Identifier of the generated order in Payu
     *
     * @var int
     */
    private $orderId;

    /**
     * Identifier of the generated transaction in Payu
     *
     * @var string
     */
    private $transactionId;

    /**
     * The Payu's transaction state
     *
     * @var string
     */
    private $transactionState;

    /**
     * Code returned by the financial network
     *
     * @var string
     */
    private $trazabilityCode;

    /**
     * If $transactionState is not APPROVED, this attribute defines the reason
     *
     * @var string
     */
    private $responseCode;

    /**
     * Message related to the $responseCode
     *
     * @var string
     */
    private $responseMessage;

    /**
     * Date of response creation in Payu
     *
     * @var string
     */
    private $transactionDate;

    /**
     * Time of response creation in Payu
     *
     * @var string
     */
    private $transactionTime;

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getTransactionState()
    {
        return $this->transactionState;
    }

    /**
     * @param string $transactionState
     */
    public function setTransactionState($transactionState)
    {
        $this->transactionState = $transactionState;
    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param string $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        if (strlen($this->responseMessage) > 0) {
            return substr($this->responseMessage, 0, 256);
        }

        if ($this->transactionIsApproved()) {
            return self::RESPONSE_MESSAGE_SUCCESS;
        }

        return self::RESPONSE_MESSAGE_ERROR_DEFAULT;
    }

    /**
     * @param string $responseMessage
     */
    public function setResponseMessage($responseMessage)
    {
        $responseMessage = substr($responseMessage, 0, 256);
        $this->responseMessage = $responseMessage;
    }

    /**
     * @return string
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @param string $transactionDate
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;
    }

    /**
     * @return string
     */
    public function getTransactionTime()
    {
        return $this->transactionTime;
    }

    /**
     * @param string $transactionTime
     */
    public function setTransactionTime($transactionTime)
    {
        $this->transactionTime = $transactionTime;
    }

    /**
     * @return string
     */
    public function getTrazabilityCode()
    {
        return $this->trazabilityCode;
    }

    /**
     * @param string $trazabilityCode
     */
    public function setTrazabilityCode($trazabilityCode)
    {
        $this->trazabilityCode = $trazabilityCode;
    }

    /**
     * Defines whether or not the transaction was successful
     *
     * @return bool
     */
    public function transactionIsApproved()
    {
        return $this->getTransactionState() === self::TRANSACTION_STATE_APPROVED;
    }
}
