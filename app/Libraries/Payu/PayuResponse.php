<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/24/18
 * Time: 1:01 AM
 */

namespace App\Libraries\Payu;

class PayuResponse
{
    /**
     * General response code SUCCESS | ERROR
     *
     * @var string
     */
    private $code;

    /**
     * Error message in case there is
     *
     * @var string
     */
    private $error;

    const CODE_ERROR = 'ERROR';

    const CODE_SUCCESS = 'SUCCESS';

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Returns true if the request was successfuly executed
     * This doesn't mean the operation was successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getCode() === self::CODE_SUCCESS;
    }
}
