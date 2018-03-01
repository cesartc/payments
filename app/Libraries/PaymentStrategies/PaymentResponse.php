<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/26/18
 * Time: 7:14 PM
 */

namespace App\Libraries\PaymentStrategies;

interface PaymentResponse
{
    const RESPONSE_MESSAGE_SUCCESS = 'El pedido se realizó correctamente.';

    const RESPONSE_MESSAGE_ERROR_DEFAULT = 'Ocurrió un error al generar el pedido.';

    /**
     * Defines whether or not the transaction was successful.
     * This means, the payment has been made. Money has been transfered.
     *
     * @return bool
     */
    public function transactionIsApproved();

    /**
     * Identifier of the transaction on the payment vendor side
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Returns the response code of the operation sent by the vendor
     *
     * @return string
     */
    public function getResponseCode();

    /**
     * Response message sent by the vendor. This is supposed to define
     * what the response code refers to
     *
     * @return string
     */
    public function getResponseMessage();

    /**
     * The order ID generated on the vendor side. This can represent
     * any other value depending on the vendor
     *
     * @return string
     */
    public function getOrderId();
}
