<?php namespace App\Models;

use Illuminate\Support\Facades\Validator;
use App\Models\BaseModels\BaseModel;

class Order extends BaseModel
{
    const STATUS_INICIAL           = 'INICIAL';
    const STATUS_CAPTURADO         = 'CAPTURADO';
    const STATUS_PENDIENTE         = 'PENDIENTE';
    const STATUS_NUEVO             = 'NUEVO';
    const STATUS_ENTREGA_PENDIENTE = 'PENDIENTE ENTREGA';
    const STATUS_REEMBOLSADO       = 'REEMBOLSADO';
    const STATUS_CANCELADO         = 'CANCELADO';
    const STATUS_FINALIZADO        = 'FINALIZADO';
 
    const SOURCE_WEB     = 'WEB';
    const SOURCE_MOVIL   = 'MOVIL';
 
    const PAYMENT_METHOD_VISA       = 'VISA';
    const PAYMENT_METHOD_MASTERCARD = 'MASTERCARD';
    const PAYMENT_METHOD_DEPOSITO   = 'DEPOSITO';
    const PAYMENT_METHOD_CARD       = 'CARD';

    const ID_LENGTH = 8;

    const TYPE_ORDER    = 'ORDER';
    const TYPE_PURCHASE = 'PURCHASE';

    private $consideredOrder = [
        self::STATUS_PENDIENTE,
        self::STATUS_CAPTURADO
    ];

    private $consideredPurchase = [
        self::STATUS_NUEVO,
        self::STATUS_ENTREGA_PENDIENTE,
        self::STATUS_FINALIZADO
    ];

    public function getId()
    {
        return str_pad((string) $this->id, self::ID_LENGTH, '0', STR_PAD_LEFT);
    }

    public function getTotalAmount()
    {
        return $this->money_amount + $this->code_amount;
    }

    public function getPurchaseStatuses()
    {
        return $this->consideredPurchase;
    }

    public function getOrderStatuses()
    {
        return $this->consideredOrder;
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product')->withPivot('quantity');
    }

    public function combos()
    {
        return $this->belongsToMany('App\Models\Combo')->withPivot('quantity');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function code()
    {
        return $this->belongsTo('App\Models\Code');
    }

    public function records()
    {
        return $this->hasMany('App\Models\OrdersRecord');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    /**
     * @return float
     */
    public function getMoneyAmount()
    {
        return $this->money_amount;
    }

    public function hasValidItemsFormat(array $items)
    {
        foreach ($items as $item) {
            $hasIndexes = array_key_exists('item', $item)
                            && array_key_exists('quantity', $item);

            if (! $hasIndexes) {
                abort(400, 'At least one list of items is not right formated.');

            } else {
                if (! ctype_digit($item['quantity']) || $item['quantity'] < 0) {
                    abort(400, 'Some of the quantities are not right.');
                }
            }
        }

        return true;
    }

    public static function validateItemsRequest(array $items)
    {
        if (empty($items)) {
            abort(400, 'At least one item is required.');
        }

        foreach ($items as $item) {
            $hasIndexes = array_key_exists('id', $item)
                && array_key_exists('quantity', $item);

            if (! $hasIndexes) {
                abort(400, 'At least one list of items is not right formated.');

            } else {
                if (! is_int($item['quantity']) || $item['quantity'] < 0) {
                    abort(400, 'Some of the quantities are not right.');
                }
                if (! is_int($item['id']) || $item['id'] < 0) {
                    abort(400, 'Some of the ids are not right.');
                }
            }
        }

        return true;
    }

    public static function formatItems($params, $items)
    {
        $formated = [];

        foreach ($params as $param) {
            $formated[] = [
                'item' => $items->where('id', $param['id'])->first(),
                'quantity' => $param['quantity']
            ];
        }

        return $formated;
    }

    public function isSuccessful()
    {
        return $this->status === self::STATUS_PENDIENTE ||
            $this->status === self::STATUS_NUEVO;
    }

}
