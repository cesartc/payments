<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Exceptions\SendMailException;
use Illuminate\Support\Facades\Validator;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'active', 'profile'
    ];

    const PROFILE_USER = 'USER';

    const PROFILE_CLIENT = 'CLIENT';

    const DOC_TYPE_DNI = 'DNI';

    const DOC_TYPE_RUC = 'RUC';

    const PASSWORD_LENGTH = 8;

    const TOKEN_LENGTH = 60;

    const PROVIDER_EMAIL = 'EMAIL';

    const PROVIDER_FACEBOOK = 'FACEBOOK';

    const PROVIDER_GMAIL = 'GMAIL';

    /**
     * IP the client uses to connect
     *
     * @var string
     */
    private $ip;

    /**
     * The agent the client is using to connect
     *
     * example: Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0'
     * @var string
     */
    private $agent;

    public function orders ()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function resetToken ()
    {
        $this->token = str_random(self::TOKEN_LENGTH);
    }

    public function isValidClient()
    {
        return $this->isClient() && $this->active;
    }

    public function isClient ()
    {
        return $this->profile == self::PROFILE_CLIENT;
    }

    public function usesEmailLogin()
    {
        return $this->provider == self::PROVIDER_EMAIL;
    }

    public static function getEmailLoginValidator (array $input)
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        return Validator::make($input, $rules);
    }

    public static function getUpdateUserValidator (array $input)
    {
        $rules = [
            'email' => 'required|email',
            'first_name' => 'required',
            'last_name' => 'required'
        ];

        if (! empty($input['doc_type']) || ! empty($input['doc_number'])) {
            $rules['doc_type'] = 'required|in:'
                . self::DOC_TYPE_DNI . ',' .self::DOC_TYPE_RUC;

            $documentLength = '0';
            if ($input['doc_type'] == self::DOC_TYPE_DNI) {
                $documentLength = '8';

            } elseif ($input['doc_type'] == self::DOC_TYPE_RUC) {
                $documentLength = '11';
            }
            $rules['doc_number'] = 'required|digits:' . $documentLength;
        } else {
            $rules['doc_number'] = 'size:0';
        }

        return Validator::make($input, $rules);
    }

    public function getUserByTokenValidator (array $input)
    {
        $rules = [
            'token' => 'required'
        ];

        return Validator::make($input, $rules);
    }

    public static function getOrdersValidator(array $input)
    {
        $rules = [
            'type' => 'required|in:'
                . Order::TYPE_ORDER . ',' . Order::TYPE_PURCHASE
        ];

        return Validator::make($input, $rules);
    }

    public function getFullName()
    {
        return $this->first_name . ' ' + $this->last_name;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param string $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }
}
