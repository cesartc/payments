<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/25/18
 * Time: 1:26 AM
 */

namespace App\Libraries\Payu;

use App\Exceptions\Payu\NotPayuRequestSubClassException;
use Illuminate\Container\Container;
use GuzzleHttp\Client;

class PayuRequestContainer
{
    /**
     * Returns an instance of a PayuRequest
     * with the http client already injected
     *
     * @param $payuRequestClass string Class to be instanciated
     * @return PayuRequest
     * @throws NotPayuRequestSubClassException
     */
    public static function get($payuRequestClass)
    {
        $container = Container::getInstance();
        try {
            $requestInstace = $container->make(
                $payuRequestClass,
                [
                    'httpClient' => new Client([
                        'base_uri' => env('PAYU_BASE_URI'),
                        'headers' => [
                            'Content-Type' => env('PAYU_HEADERS_CONTENT_TYPE'),
                            'Accept' => env('PAYU_HEADERS_ACCEPT'),
                        ]
                    ])
                ]
            );
            return $requestInstace;
        } catch (\Exception $e) {
            throw new NotPayuRequestSubClassException();
        }
    }
}
