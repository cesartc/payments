<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/25/18
 * Time: 1:46 AM
 */

namespace Tests\Payu;

use App\Libraries\Payu\AuthAndCaptureResponse;
use App\Libraries\Payu\PayuRequestContainer;
use App\Libraries\Payu\PayuRequest;
use App\Exceptions\Payu\NotPayuRequestSubClassException;

class PayuRequestContainerTest extends \TestCase
{
    public function testGetReturnsInstanceOfPayuRequestForChildClass()
    {
        $request = PayuRequestContainer::get(AuthAndCaptureResponse::class);

        $this->isInstanceOf(PayuRequest::class, $request);
    }

    public function testThrowsExceptionWhenNoChildClassIsPassed()
    {
        $this->setExpectedException(NotPayuRequestSubClassException::class);
        PayuRequestContainer::get('NoPayuRequestChildClass');
    }
}
