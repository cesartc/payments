<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/18/18
 * Time: 10:21 AM
 */

namespace App\Models;

use App\Exceptions\MethodNotFoundForPaymentCardNumberException;

class PaymentCard
{
    /**
     * Name in the card
     *
     * @var string
     */
    private $name;

    /**
     * Document type of the card owner
     *
     * @var string
     */
    private $docType;

    /**
     * Document number of the card owner
     *
     * @var string
     */
    private $docNumber;

    /**
     * Number of the card
     *
     * @var string
     */
    private $number;

    /**
     * Security code of the card
     *
     * @var string
     */
    private $securityCode;

    /**
     * Expiration date in the format YYYY/MM
     *
     * @var string
     */
    private $expirationDate;

    /**
     * Type of the credit card (VISA, MASTERCARD, etc)
     *
     * @var string
     */
    private $method;

    const METHOD_VISA = 'VISA';

    const REGEX_VISA = '/^(4)(\\d{12}|\\d{15})$|^(606374\\d{10}$)/';

    const METHOD_VISA_DEBIT = 'VISA_DEBIT';

    const REGEX_VISA_DEBIT = '/\\d{16}$, false/';

    const METHOD_MASTERCARD = 'MASTERCARD';

    const REGEX_MASTERCARD = '/^(5[1-5]\\d{14}$)|^(2(?:2(?:2[1-9]|[3-9]\\d)|[3-6]\\d\\d|7(?:[01]\\d|20))\\d{12}$)/';

    const METHOD_MASTERCARD_DEBIT = 'MASTERCARD_DEBIT';

    const REGEX_MASTERCARD_DEBIT = '/\\d{16}$”, false/';

    const METHOD_DINERS = 'DINERS';

    const REGEX_DINERS = '/(^[35](?:0[0-5]|[68][0-9])[0-9]{11}$)|(^30[0-5]{11}$)|(^3095(\\d{10})$)|(^36{12}$)|(^3[89](\\d{12})$)/';

    const METHOD_AMEX = 'AMEX';

    const REGEX_AMEX = '/^(3[47]\\d{13})$/';

    private $methodsArray = [
        self::METHOD_VISA => self::REGEX_VISA,
        self::METHOD_VISA_DEBIT => self::REGEX_VISA_DEBIT,
        self::METHOD_MASTERCARD => self::REGEX_MASTERCARD,
        self::METHOD_MASTERCARD_DEBIT => self::REGEX_MASTERCARD_DEBIT,
        self::METHOD_DINERS => self::REGEX_DINERS,
        self::METHOD_AMEX => self::REGEX_AMEX
    ];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDocType()
    {
        return $this->docType;
    }

    /**
     * @param string $docType
     */
    public function setDocType($docType)
    {
        $this->docType = $docType;
    }

    /**
     * @return string
     */
    public function getDocNumber()
    {
        return $this->docNumber;
    }

    /**
     * @param string $docNumber
     */
    public function setDocNumber($docNumber)
    {
        $this->docNumber = $docNumber;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * @param string $securityCode
     */
    public function setSecurityCode($securityCode)
    {
        $this->securityCode = $securityCode;
    }

    /**
     * @return string
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param string $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * Returns the card method based on the number structure;
     *
     * @return string
     * @throws MethodNotFoundForPaymentCardNumberException
     */
    public function getMethod()
    {
        foreach ($this->methodsArray as $method => $regex) {
            if (preg_match($regex, $this->number)) {
                return $method;
            }
        }

        throw new MethodNotFoundForPaymentCardNumberException();
    }
}
